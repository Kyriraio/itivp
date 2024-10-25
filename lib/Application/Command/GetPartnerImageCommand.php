<?php
namespace Application\Command;

use Exception;

class GetPartnerImageCommand {
    private string $imagePath;

    public function __construct() {
        // Укажите путь к папке с изображениями
        $this->imagePath = __DIR__ . '/../../../images/download.png'; // Измените на ваше имя файла
    }

    /**
     * Возвращает изображение партнёра или ошибку.
     *
     * @throws Exception
     */
    public function execute(): string {
        // Проверяем, существует ли файл изображения
        if (!file_exists($this->imagePath)) {
            throw new Exception('Image not found.'); // Возвращаем ошибку, если изображение не найдено
        }
        return base64_encode(file_get_contents($this->imagePath));
    }
}
