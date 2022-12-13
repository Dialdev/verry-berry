<?php
require_once('../bitrix/modules/main/include/prolog_before.php');

global $DB;
global $USER;

$phone = $_POST['phone'] ?? false;

$login = $_POST['login'] ?? false;

$code = $_POST['code'] ?? false;

if ($code) {
    if (checkCode($code, $login)) {
        $userDB = CUser::GetByLogin($login)->Fetch();

        $USER->Authorize($userDB['ID'], true);

        sendRedirect();
    }
    else
        sendErrorJSON('Код некорректный');
}

if (!$phone or !is_numeric($phone))
    throw new Exception('Некорретный телефон');

$users = getAllUserWithPhone();

$users = findAllUsersByPhone($users, $phone);

if (!$users)
    sendErrorJSON('Не найден пользователь по указанному телефону');

if (count($users) > 1) {
    if ($login) {
        $user = findUserByLogin($users, $login);

        if (!$user)
            sendErrorNotOneUser($users);
    }
    else
        sendErrorNotOneUser($users);
}
else
    $user = current($users);

$call = sendCall($phone);

if ($call->status == 'error')
    sendErrorJSON($call->message);

$DB->Query("INSERT INTO b_auth_phone SET
                          login = '$user[LOGIN]',
                          code = {$call->code}
                       ON DUPLICATE KEY UPDATE 
                           code = VALUES(code)");


sendSuccessJSON('Звонок отправлен в очередь', $user);

function checkCode(string $code, string $login): bool
{
    global $DB;

    if (!$code or !$login)
        throw new Exception('Не передан код илил логин');

    $code = str_replace("'", '', $code);

    $login = str_replace("'", '', $login);

    $whereQuery = "login = '$login' AND code = '$code'";

    $result = $DB->Query("SELECT * FROM b_auth_phone WHERE $whereQuery LIMIT 1")->Fetch();

    if (!$result)
        return false;

    $DB->Query("DELETE FROM b_auth_phone WHERE $whereQuery");

    return true;
}

function sendCall(int $phone): stdClass
{
    $client = new GuzzleHttp\Client([
        'connect_timeout' => $timeout = 15,
        'timeout'         => $timeout,
        'allow_redirects' => false,
        'http_errors'     => true,
        'verify'          => false,
    ]);

    $response = $client->request('GET', "https://a.hi-call.ru/call/530d3f2e-dff8-421e-a1ce-4ad453e98dd1/$phone/");

    return json_decode($response->getBody()->getContents());
}

function sendErrorNotOneUser(array $users)
{
    $data = [];

    foreach ($users as $user) {
        $data[] = (object)[
            'ID'    => $user['ID'],
            'LOGIN' => $user['LOGIN'],
            'NAME'  => $user['NAME'],
        ];
    }

    $error = (object)[
        'error'   => 1,
        'message' => 'На этот телефон зарегистрировано несколько пользователей. Выберите одного для авторизации',
        'data'    => $data,
    ];

    sendJSON($error);
}

function sendRedirect(): void
{
    $success = (object)[
        'success' => 301,
        'message' => 'Redirect',
    ];

    sendJSON($success);
}

function sendSuccessJSON(string $message, array $user, int $code = 1): void
{
    $success = (object)[
        'success' => $code,
        'message' => $message,
        'user'    => (object)[
            'ID'    => $user['ID'],
            'LOGIN' => $user['LOGIN'],
            'NAME'  => $user['NAME'],
        ],
    ];

    sendJSON($success);
}

function sendErrorJSON(string $message, int $code = 2)
{
    $error = (object)[
        'error'   => $code,
        'message' => $message,
    ];

    sendJSON($error);
}

function sendJSON(stdClass $answer)
{
    header('Content-Type: application/json');

    echo json_encode($answer);

    exit;
}

function findUserByLogin(array $users, string $login): array
{
    return current(array_filter($users, fn($user): bool => $user['LOGIN'] == $login)) ?: [];
}

function findAllUsersByPhone(array $users, int $phone): array
{
    return array_values(array_filter($users, fn($user): bool => $user['PERSONAL_PHONE'] == $phone));
}

function getAllUserWithPhone(): array
{
    $users = CUser::GetList(($by = "ID"), ($order = "desc"));

    $resultUsers = [];

    while ($user = $users->GetNext()) {
        $user['PERSONAL_PHONE'] = trim(preg_replace('~\D+~', '', $user['PERSONAL_PHONE']));

        if (!$user['PERSONAL_PHONE'])
            continue;

        $resultUsers[] = [
            'ID'             => $user['ID'],
            'LOGIN'          => $user['LOGIN'],
            'PASSWORD'       => $user['PASSWORD'],
            'ACTIVE'         => $user['ACTIVE'],
            'NAME'           => $user['NAME'],
            'EMAIL'          => $user['EMAIL'],
            'PERSONAL_PHONE' => $user['PERSONAL_PHONE'],
        ];
    }

    return $resultUsers;
}




