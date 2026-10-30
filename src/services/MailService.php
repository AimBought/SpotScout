<?php

declare(strict_types=1);

namespace App\Service;

use RuntimeException;

final class MailService
{
    private string $host;
    private int $port;
    private string $from;
    private string $fromName;

    public function __construct()
    {
        require_once dirname(__DIR__, 2) . '/config.php';

        $this->host = MAIL_HOST;
        $this->port = MAIL_PORT;
        $this->from = MAIL_FROM;
        $this->fromName = MAIL_FROM_NAME;
    }

    public function sendWelcomeEmail(string $to, string $fullName): void
    {
        if (filter_var($to, FILTER_VALIDATE_EMAIL) === false) {
            throw new RuntimeException('Nieprawidłowy adres odbiorcy wiadomości.');
        }

        $subject = 'Witamy w SpotScout — cieszymy się, że jesteś!';
        $body = "Cześć {$fullName},\r\n\r\n"
            . "Witamy w SpotScout! Twoje konto jest gotowe. Odkrywaj wyjątkowe lokacje "
            . "i dziel się nimi z innymi fotografami oraz filmowcami.\r\n\r\n"
            . "Do zobaczenia w terenie,\r\nZespół SpotScout";

        $this->sendEmail($to, $subject, $body);
    }

    private function sendEmail(string $to, string $subject, string $body): void
    {
        $errorNumber = 0;
        $errorMessage = '';
        $socket = @fsockopen($this->host, $this->port, $errorNumber, $errorMessage, 10);
        if ($socket === false) {
            throw new RuntimeException(
                sprintf('Nie można połączyć się z serwerem SMTP: %s (%d).', $errorMessage, $errorNumber)
            );
        }

        stream_set_timeout($socket, 10);

        try {
            $this->readResponse($socket, [220]);
            $this->sendEhlo($socket);
            $this->sendMailFrom($socket);
            $this->sendRcptTo($socket, $to);
            $this->sendData($socket, $to, $subject, $body);
            $this->sendQuit($socket);
        } finally {
            fclose($socket);
        }
    }

    /** @param resource $socket */
    private function sendEhlo(mixed $socket): void
    {
        $this->sendCommand($socket, 'EHLO spotscout.local', [250]);
    }

    /** @param resource $socket */
    private function sendMailFrom(mixed $socket): void
    {
        $this->sendCommand($socket, 'MAIL FROM:<' . $this->from . '>', [250]);
    }

    /** @param resource $socket */
    private function sendRcptTo(mixed $socket, string $to): void
    {
        $this->sendCommand($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
    }

    /** @param resource $socket */
    private function sendData(mixed $socket, string $to, string $subject, string $body): void
    {
        $this->sendCommand($socket, 'DATA', [354]);

        $encodedFromName = '=?UTF-8?B?' . base64_encode($this->fromName) . '?=';
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $headers = [
            'From: ' . $encodedFromName . ' <' . $this->from . '>',
            'To: <' . $to . '>',
            'Subject: ' . $encodedSubject,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
        ];
        $message = implode("\r\n", $headers) . "\r\n\r\n"
            . chunk_split(base64_encode($body), 76, "\r\n")
            . ".\r\n";

        $this->write($socket, $message);
        $this->readResponse($socket, [250]);
    }

    /** @param resource $socket */
    private function sendQuit(mixed $socket): void
    {
        $this->sendCommand($socket, 'QUIT', [221]);
    }

    /** @param resource $socket @param list<int> $expectedCodes */
    private function sendCommand(mixed $socket, string $command, array $expectedCodes): void
    {
        $this->write($socket, $command . "\r\n");
        $this->readResponse($socket, $expectedCodes);
    }

    /** @param resource $socket @param list<int> $expectedCodes */
    private function readResponse(mixed $socket, array $expectedCodes): string
    {
        $response = '';
        do {
            $line = fgets($socket, 515);
            if ($line === false) {
                throw new RuntimeException('Nie udało się odczytać odpowiedzi serwera SMTP.');
            }

            $response .= $line;
            if (preg_match('/^(\d{3})([ -])/', $line, $matches) !== 1) {
                throw new RuntimeException('Serwer SMTP zwrócił nieprawidłową odpowiedź.');
            }
            $code = (int) $matches[1];
        } while ($matches[2] === '-');

        if (!in_array($code, $expectedCodes, true)) {
            throw new RuntimeException('Błąd SMTP: ' . trim($response));
        }

        return $response;
    }

    /** @param resource $socket */
    private function write(mixed $socket, string $data): void
    {
        $length = strlen($data);
        $offset = 0;

        while ($offset < $length) {
            $written = fwrite($socket, substr($data, $offset));
            if ($written === false || $written === 0) {
                throw new RuntimeException('Nie udało się wysłać danych do serwera SMTP.');
            }
            $offset += $written;
        }
    }
}
