<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Application,
    Bitrix\Main\UserTable,
    Bitrix\Main\Context,
    Bitrix\Main\SystemException,
    Bitrix\Main\Engine\ActionFilter\Authentication,
    Bitrix\Main\Engine\ActionFilter;

use Bitrix\Main\Engine\Contract\Controllerable;
class PersonalSettings extends CBitrixComponent implements Controllerable
{

    public function configureActions()
    {
        return [
            'ChangeSubs' => [ // Ajax-метод
                'prefilters' => [],
                ],
            'UpdateUser' => [ // Ajax-метод
                'prefilters' => [],
            ],
        ];
    }
    private $fields = [
        'NAME',
        'LAST_NAME',
        'EMAIL',
        'PERSONAL_PHONE',
    ];

    private $passwordsFields = [
        'CURRENT_PASSWORD',
        'OLD_PASSWORD',
        'PASSWORD',
        'CONFIRM_PASSWORD'
    ];

    private function getUserInfo()
    {
        $this->arResult['INFO'] = CUser::GetList($by = "id", $order = "asc", ['ID' => $GLOBALS['USER']->GetID()], [
            'SELECT' => ['UF_SUBSCRIBE', 'UF_NOTIFY'],
            'FIELDS' => ['EMAIL', 'NAME', 'LAST_NAME', 'PERSONAL_PHONE', 'ID']
        ])->Fetch();
    }

    private function checkAccess()
    {
        if (!$GLOBALS['USER']->IsAuthorized()) {
            LocalRedirect("/?open_auth=Y");
        }
    }
    public static function isUserPassword($password,$userId){

        $rsUser = \CUser::GetByID($userId);
        $userData = $rsUser->Fetch();
        $salt = substr($userData['PASSWORD'], 0, (strlen($userData['PASSWORD']) - 32));
        $realPassword = substr($userData['PASSWORD'], -32);
        $password = md5($salt.$password);
        return ($password == $realPassword);

    }
    private function savePasswordCheckOld($oldPassword,$newPassword,$newPasswordConfirm,$userId){

        if(self::isUserPassword($oldPassword,$userId)){
            if($newPassword==$newPasswordConfirm){
                global $USER;
                $UserId = $USER->GetID();
                $user = new \CUser;
                $arFields = Array(
                    "PASSWORD" => $newPassword,
                    "CONFIRM_PASSWORD" => $newPasswordConfirm,
                );
                $ID = $user->Update($UserId, $arFields);
                if (intval($ID) > 0){
                    return ['MESSAGE'=>'Пароль успешно обновлен','STATUS'=>'SUCCESS'];

                }
                else{
                    return ['MESSAGE'=>$user->LAST_ERROR,'STATUS'=>'ERROR'];

                }
            }
            else{
                return ['MESSAGE'=>'Новый пароль повторён некоректно','STATUS'=>'ERROR'];
            }
        }
        else{
            return ['MESSAGE'=>'Неверный старый пароль','STATUS'=>'ERROR'];
        }

    }


    public function ChangeSubsAction($fields)
    {

        if (!empty($fields)) {
            $user = new CUser;
            $user->Update($GLOBALS['USER']->GetID(), [$fields['NAME']=>$fields['VALUE']=='true'?1:0]);
        }
        return true;
    }


    public function UpdateUserAction($fields)
    {
        $result = [];
        if (!empty($fields)) {
            $checkpass = false;
            foreach ($fields as $key=> $field){
                if($field && in_array($key,$this->passwordsFields)){
                    $arPasswordFields[$key] = $field;
                    $checkpass = true;
                }elseif (in_array($key,$this->fields)){
                    $arFields[$key] = $field;
                }
            }
            if($checkpass){
                if($arPasswordFields['CURRENT_PASSWORD'] == $arPasswordFields['OLD_PASSWORD'] && !empty($arPasswordFields['CURRENT_PASSWORD'])){
                    $result = $this->savePasswordCheckOld($arPasswordFields['OLD_PASSWORD'],$arPasswordFields['PASSWORD'],$arPasswordFields['CONFIRM_PASSWORD'],$GLOBALS['USER']->GetID());
                    if($result['STATUS']=='SUCCESS'){
                        $user = new CUser;
                        $user->Update($GLOBALS['USER']->GetID(), $arFields);
                        $result = ['STATUS'=>'SUCCESS','MESSAGE'=>'Информация успешно обновлена'];
                    }
                }else{
                    $result = ['STATUS'=>'ERROR','MESSAGE'=>'Текущий пароль не заполнен или повторен не корректно'];
                }
            }else{
                $user = new CUser;
                $user->Update($GLOBALS['USER']->GetID(), $arFields);
                $result = ['STATUS'=>'SUCCESS','MESSAGE'=>'Информация успешно обновлена'];
            }
        }else{
            $result = ['STATUS'=>'ERROR','MESSAGE'=>'Не заполнены поля'];
        }
        return $result;
    }

    public function executeComponent()
    {
        $this->checkAccess();
      //  $this->checkRequest();
        $this->getUserInfo();


        $this->includeComponentTemplate();
    }
}
