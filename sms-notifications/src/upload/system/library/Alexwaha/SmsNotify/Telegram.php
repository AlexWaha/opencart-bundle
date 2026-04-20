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

namespace Alexwaha\SmsNotify;

final class Telegram
{
    private const ENDPOINT = 'https://api.telegram.org/bot';
    private const TIMEOUT = 10;

    /**
     * Send HTML message to Telegram chat via Bot API.
     *
     * @param  string    $token  Bot token (from @BotFather)
     * @param  string    $chatId Target chat id (group, channel or personal)
     * @param  string    $html   Message body (HTML, parse_mode=HTML)
     * @param  \Log|null $log    Optional log writer
     * @return array              Decoded API response or error payload
     */
    public static function send(string $token, string $chatId, string $html, \Log $log = null): array
    {
        $token = trim($token);
        $chatId = trim($chatId);

        if ($token === '' || $chatId === '') {
            $error = ['ok' => false, 'error' => 'missing_token_or_chat_id'];
            self::log($log, '(Telegram) send skip: ' . $error['error']);

            return $error;
        }

        $payload = json_encode([
            'chat_id' => $chatId,
            'text' => $html,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ]);

        $response = self::request($token, 'sendMessage', $payload, $log);

        $ok = !empty($response['ok']);
        self::log(
            $log,
            sprintf(
                '(Telegram) send chat:%s ok:%s desc:%s',
                $chatId,
                $ok ? '1' : '0',
                $response['description'] ?? ($response['error'] ?? '')
            )
        );

        return $response;
    }

    /**
     * Reach out to Telegram getMe endpoint to verify bot token.
     *
     * @param  string    $token Bot token
     * @param  \Log|null $log   Optional log writer
     * @return array             Decoded API response or error payload
     */
    public static function getMe(string $token, \Log $log = null): array
    {
        $token = trim($token);

        if ($token === '') {
            return ['ok' => false, 'error' => 'missing_token'];
        }

        return self::request($token, 'getMe', null, $log);
    }

    /**
     * Fetch latest updates for the bot. Used to auto-detect chat_id.
     *
     * @param  string    $token Bot token
     * @param  \Log|null $log   Optional log writer
     * @return array             Decoded API response or error payload
     */
    public static function getUpdates(string $token, \Log $log = null): array
    {
        $token = trim($token);

        if ($token === '') {
            return ['ok' => false, 'error' => 'missing_token'];
        }

        return self::request($token, 'getUpdates', null, $log);
    }

    /**
     * Extract unique chats from getUpdates payload.
     * Returns list of [id, title, type] deduped by chat.id.
     *
     * @param  array $updatesResponse Raw response from getUpdates()
     * @return array                   List of unique chats
     */
    public static function extractChats(array $updatesResponse): array
    {
        if (empty($updatesResponse['ok']) || empty($updatesResponse['result'])) {
            return [];
        }

        $chats = [];

        foreach ($updatesResponse['result'] as $update) {
            $sources = [
                $update['message']['chat'] ?? null,
                $update['edited_message']['chat'] ?? null,
                $update['channel_post']['chat'] ?? null,
                $update['my_chat_member']['chat'] ?? null,
            ];

            foreach ($sources as $chat) {
                if (!is_array($chat) || !isset($chat['id'])) {
                    continue;
                }

                $id = (string) $chat['id'];

                if (isset($chats[$id])) {
                    continue;
                }

                $type = (string) ($chat['type'] ?? 'unknown');
                $title = $chat['title']
                    ?? trim(($chat['first_name'] ?? '') . ' ' . ($chat['last_name'] ?? ''))
                    ?: ($chat['username'] ?? $id);

                $chats[$id] = [
                    'id' => $id,
                    'title' => (string) $title,
                    'type' => $type,
                ];
            }
        }

        return array_values($chats);
    }

    /**
     * Internal cURL wrapper. Returns decoded JSON or error payload.
     *
     * @param  string      $token
     * @param  string      $method
     * @param  string|null $jsonBody POST body when present, GET when null
     * @param  \Log|null   $log
     * @return array
     */
    private static function request(string $token, string $method, ?string $jsonBody = null, \Log $log = null): array
    {
        $url = self::ENDPOINT . $token . '/' . $method;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::TIMEOUT);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        if ($jsonBody !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonBody),
            ]);
        }

        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            self::log($log, '(Telegram) curl error: ' . $curlErr);

            return ['ok' => false, 'error' => 'curl_error', 'description' => $curlErr];
        }

        $decoded = json_decode($body, true);

        if (!is_array($decoded)) {
            self::log($log, '(Telegram) invalid json (http ' . $httpCode . '): ' . substr((string) $body, 0, 200));

            return ['ok' => false, 'error' => 'invalid_json', 'http_code' => $httpCode];
        }

        return $decoded;
    }

    private static function log(?\Log $log, string $message): void
    {
        if ($log instanceof \Log) {
            $log->write($message);
        }
    }
}
