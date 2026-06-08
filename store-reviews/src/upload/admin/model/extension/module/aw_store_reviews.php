<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ModelExtensionModuleAwStoreReviews extends Model
{
    public function createTable(): void
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "aw_review` (
            `review_id` INT AUTO_INCREMENT PRIMARY KEY,
            `author` VARCHAR(64) NOT NULL,
            `city` VARCHAR(64) NOT NULL DEFAULT '',
            `text` TEXT NOT NULL,
            `rating` INT(1) NOT NULL DEFAULT 5,
            `status` TINYINT(1) NOT NULL DEFAULT 0,
            `date_added` DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    public function dropTable(): void
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "aw_review`");
    }

    public function addReview(array $data): int
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "aw_review` SET
            author = '" . $this->db->escape($data['author']) . "',
            city = '" . $this->db->escape($data['city'] ?? '') . "',
            `text` = '" . $this->db->escape($data['text']) . "',
            rating = '" . (int) ($data['rating'] ?? 5) . "',
            status = '" . (int) ($data['status'] ?? 0) . "',
            date_added = '" . $this->db->escape($data['date_added'] ?? date('Y-m-d H:i:s')) . "'");

        return $this->db->getLastId();
    }

    public function editReview(int $reviewId, array $data): void
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "aw_review` SET
            author = '" . $this->db->escape($data['author']) . "',
            city = '" . $this->db->escape($data['city'] ?? '') . "',
            `text` = '" . $this->db->escape($data['text']) . "',
            rating = '" . (int) ($data['rating'] ?? 5) . "',
            status = '" . (int) ($data['status'] ?? 0) . "'
            WHERE review_id = '" . (int) $reviewId . "'");
    }

    public function deleteReview(int $reviewId): void
    {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "aw_review`
            WHERE review_id = '" . (int) $reviewId . "'");
    }

    public function deleteAllReviews(): void
    {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "aw_review`");
    }

    public function getReview(int $reviewId): array
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "aw_review`
            WHERE review_id = '" . (int) $reviewId . "'");

        return $query->row ?: [];
    }

    public function getReviews(array $data = []): array
    {
        $sql = "SELECT * FROM `" . DB_PREFIX . "aw_review` WHERE 1";

        if (isset($data['filter_status']) && $data['filter_status'] !== '') {
            $sql .= " AND status = '" . (int) $data['filter_status'] . "'";
        }

        $sortFields = ['author', 'city', 'rating', 'status', 'date_added'];
        $sort = isset($data['sort']) && in_array($data['sort'], $sortFields, true)
            ? $data['sort'] : 'date_added';

        $order = isset($data['order']) && strtoupper($data['order']) === 'ASC' ? 'ASC' : 'DESC';

        $sql .= " ORDER BY `" . $sort . "` " . $order;

        if (isset($data['start']) || isset($data['limit'])) {
            $start = max(0, (int) ($data['start'] ?? 0));
            $limit = max(1, (int) ($data['limit'] ?? 20));
            $sql .= " LIMIT " . $start . ", " . $limit;
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalReviews(array $data = []): int
    {
        $sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "aw_review` WHERE 1";

        if (isset($data['filter_status']) && $data['filter_status'] !== '') {
            $sql .= " AND status = '" . (int) $data['filter_status'] . "'";
        }

        $query = $this->db->query($sql);

        return (int) ($query->row['total'] ?? 0);
    }
}
