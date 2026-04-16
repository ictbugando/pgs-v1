<?php
namespace App\Libraries;

use Exception;
use InvalidArgumentException;

class MkcbAesGcm
{
    private int $ivLength   = 12;
    private int $saltLength = 16;
    private int $tagLength  = 16;
    private int $iterations = 65536;
    private string $kdfAlgo = 'sha256';
    private string $cipher  = 'aes-256-gcm';
    private string $password;

    public function __construct(string $password)
    {
        if (empty($password)) {
            throw new InvalidArgumentException("Encryption key (password) cannot be empty");
        }
        $this->password = $password;
    }

    public function encrypt(array $data): string
    {
        $iv = random_bytes($this->ivLength);
        $salt = random_bytes($this->saltLength);

        $key = hash_pbkdf2($this->kdfAlgo, $this->password, $salt, $this->iterations, 32, true);
        $plaintext = json_encode($data, JSON_UNESCAPED_UNICODE);

        $tag = '';
        $ciphertext = openssl_encrypt(
            $plaintext,
            $this->cipher,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            $this->tagLength
        );

        if ($ciphertext === false) {
            throw new Exception('Encryption failed');
        }

        $envelope = $iv . $salt . $ciphertext . $tag;
        return json_encode(['data' => base64_encode($envelope)]);
    }

    public function decrypt(string $jsonInput)
    {
        $parsed = json_decode($jsonInput, true);
        if (!isset($parsed['data'])) {
            throw new Exception("Invalid data format");
        }

        $decoded = base64_decode($parsed['data'], true);
        if ($decoded === false) {
            throw new Exception("Invalid base64 data");
        }

        $iv = substr($decoded, 0, $this->ivLength);
        $salt = substr($decoded, $this->ivLength, $this->saltLength);
        $tag = substr($decoded, -$this->tagLength);
        $ciphertext = substr($decoded, $this->ivLength + $this->saltLength, -$this->tagLength);

        $key = hash_pbkdf2($this->kdfAlgo, $this->password, $salt, $this->iterations, 32, true);

        $plaintext = openssl_decrypt(
            $ciphertext,
            $this->cipher,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($plaintext === false) {
            throw new Exception('Decryption failed — invalid tag or wrong key');
        }

        $decodedPlain = json_decode($plaintext, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $decodedPlain : $plaintext;
    }
}
