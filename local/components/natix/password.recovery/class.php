<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\UserTable,
    Bitrix\Main\Engine\ActionFilter\Authentication,
    Bitrix\Main\Engine\ActionFilter;



use Bitrix\Main\Engine\Contract\Controllerable;

class recovery extends \CBitrixComponent implements Controllerable
{


    public function configureActions()
    {
        return [
            'SendCode' => [ // Ajax-метод
                'prefilters' => [],
            ],
            'CheckCode' => [ // Ajax-метод
                'prefilters' => [],
            ],
            'NewPassword' => [ // Ajax-метод
                'prefilters' => [],
            ],
        ];
    }
    public function SendCodeAction($email)
    {
        $arFilter = ['EMAIL'=>$email];
        $res = UserTable::getList(Array(
            "select"=>Array("ID","NAME"),
            "filter"=>$arFilter,
        ));
        $arUser = $res->fetch();
        if($arUser){
            $code = randString(4,['0123456789']);
            $_SESSION['code'] = $code;
            $_SESSION['user_id'] = $arUser['ID'];
            $arEventFields = ['CODE' => $code,'EMAIL'=>$email];
            CEvent::Send("MY_FORGOT_PASSWORD", SITE_ID, $arEventFields,'N');
            $result = ['CODE'=>$code,'STATUS'=>'SUCCESS'];

        }else{
            $result = ['MESSAGE'=>'Пользователь не найден','STATUS'=>'ERROR'];
        }


        return $result;
    }
    public function CheckCodeAction($code)
    {

        if($code == $_SESSION['code']){
            $result = ['STATUS'=>'SUCCESS'];
            unset($_SESSION['code']);
        }else{
            $result = ['MESSAGE'=>'Неверный код','STATUS'=>'ERROR'];
        }
        return $result;
    }
    public function NewPasswordAction($newPassword,$newPasswordConfirm)
    {
        if(!$_SESSION['user_id']){
            return ['MESSAGE'=>'Сессия истекла, повторите процедуру','STATUS'=>'ERROR'];
        }else{
            if($newPassword==$newPasswordConfirm){

                $UserId =  $_SESSION['user_id'];
                $user = new \CUser;
                $arFields = Array(
                    "PASSWORD" => $newPassword,
                    "CONFIRM_PASSWORD" => $newPasswordConfirm,
                );
                $ID = $user->Update($UserId, $arFields);
                if (intval($ID) > 0){
                    return ['MESSAGE'=>'Пароль успешно изменён','STATUS'=>'SUCCESS'];

                }
                else{
                    return ['MESSAGE'=>$user->LAST_ERROR,'STATUS'=>'ERROR'];

                }
            }
            else{
                return ['MESSAGE'=>'Новый пароль повторён некоректно','STATUS'=>'ERROR'];
            }
        }

    }



    public function executeComponent()
    {

        $this->includeComponentTemplate();

    }

}
