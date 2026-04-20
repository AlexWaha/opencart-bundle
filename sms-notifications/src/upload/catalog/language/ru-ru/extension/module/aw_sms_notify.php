<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 *
 * @link    https://alexwaha.com
 *
 * @email   support@alexwaha.com
 *
 * @license GPLv3
 */

// Buttons
$_['button_otp_resend']           = 'Запросить код повторно';
$_['button_otp_verify']           = 'Подтвердить';

// Entries
$_['entry_phone']                 = 'Телефон';

// Texts
$_['text_otp_modal_title']        = 'Подтверждение телефона';
$_['text_otp_modal_text']         = 'Введите 6-значный код, отправленный по SMS.';
$_['text_otp_code_sent']          = 'Код отправлен на ваш телефон';

// Errors
$_['error_otp_required']          = 'Требуется подтверждение телефона';
$_['error_otp_invalid_code']      = 'Неверный код';
$_['error_otp_expired']           = 'Срок действия кода истёк';
$_['error_otp_throttle']          = 'Подождите %s секунд перед повторной отправкой';
$_['error_otp_attempts_exceeded'] = 'Слишком много попыток. Запросите новый код.';
$_['error_otp_invalid_phone']     = 'Неверный номер телефона';
$_['error_otp_gateway']           = 'Не удалось отправить SMS. Попробуйте позже.';
$_['error_otp_lockout']           = 'Слишком много запросов кода. Попробуйте позже.';
