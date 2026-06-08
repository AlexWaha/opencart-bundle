<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

class ModelExtensionModuleAwFaq extends Model
{
    public function createTables(): void
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "aw_faq` (
            `faq_id` INT AUTO_INCREMENT PRIMARY KEY,
            `sort_order` INT NOT NULL DEFAULT 0,
            `status` TINYINT(1) NOT NULL DEFAULT 1,
            `date_added` DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "aw_faq_description` (
            `faq_id` INT NOT NULL,
            `language_id` INT NOT NULL,
            `question` VARCHAR(500) NOT NULL,
            `answer` TEXT NOT NULL,
            PRIMARY KEY (`faq_id`, `language_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    public function dropTables(): void
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "aw_faq_description`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "aw_faq`");
    }

    public function addFaq(array $data): int
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "aw_faq` SET
            sort_order = '" . (int) ($data['sort_order'] ?? 0) . "',
            status = '" . (int) ($data['status'] ?? 1) . "',
            date_added = NOW()");

        $faqId = $this->db->getLastId();

        if (isset($data['faq_description'])) {
            foreach ($data['faq_description'] as $languageId => $value) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "aw_faq_description` SET
                    faq_id = '" . (int) $faqId . "',
                    language_id = '" . (int) $languageId . "',
                    question = '" . $this->db->escape($value['question']) . "',
                    answer = '" . $this->db->escape($value['answer']) . "'");
            }
        }

        return $faqId;
    }

    public function editFaq(int $faqId, array $data): void
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "aw_faq` SET
            sort_order = '" . (int) ($data['sort_order'] ?? 0) . "',
            status = '" . (int) ($data['status'] ?? 1) . "'
            WHERE faq_id = '" . (int) $faqId . "'");

        $this->db->query("DELETE FROM `" . DB_PREFIX . "aw_faq_description`
            WHERE faq_id = '" . (int) $faqId . "'");

        if (isset($data['faq_description'])) {
            foreach ($data['faq_description'] as $languageId => $value) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "aw_faq_description` SET
                    faq_id = '" . (int) $faqId . "',
                    language_id = '" . (int) $languageId . "',
                    question = '" . $this->db->escape($value['question']) . "',
                    answer = '" . $this->db->escape($value['answer']) . "'");
            }
        }
    }

    public function deleteFaq(int $faqId): void
    {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "aw_faq_description`
            WHERE faq_id = '" . (int) $faqId . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "aw_faq`
            WHERE faq_id = '" . (int) $faqId . "'");
    }

    public function getFaq(int $faqId): array
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "aw_faq`
            WHERE faq_id = '" . (int) $faqId . "'");

        if (!$query->row) {
            return [];
        }

        $faq = $query->row;

        $descQuery = $this->db->query("SELECT * FROM `" . DB_PREFIX . "aw_faq_description`
            WHERE faq_id = '" . (int) $faqId . "'");

        $faq['descriptions'] = [];

        foreach ($descQuery->rows as $row) {
            $faq['descriptions'][$row['language_id']] = [
                'question' => $row['question'],
                'answer'   => $row['answer'],
            ];
        }

        return $faq;
    }

    public function getFaqs(array $data = []): array
    {
        $sql = "SELECT f.*, fd.question FROM `" . DB_PREFIX . "aw_faq` f
            LEFT JOIN `" . DB_PREFIX . "aw_faq_description` fd
                ON (f.faq_id = fd.faq_id AND fd.language_id = '" . (int) $this->config->get('config_language_id') . "')
            WHERE 1";

        $sortFields = ['sort_order', 'status', 'date_added'];
        $sort = isset($data['sort']) && in_array($data['sort'], $sortFields, true)
            ? $data['sort'] : 'sort_order';

        $order = isset($data['order']) && strtoupper($data['order']) === 'DESC' ? 'DESC' : 'ASC';

        $sql .= " ORDER BY f.`" . $sort . "` " . $order;

        if (isset($data['start']) || isset($data['limit'])) {
            $start = max(0, (int) ($data['start'] ?? 0));
            $limit = max(1, (int) ($data['limit'] ?? 20));
            $sql .= " LIMIT " . $start . ", " . $limit;
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalFaqs(): int
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "aw_faq`");

        return (int) ($query->row['total'] ?? 0);
    }

    public function updateSortOrder(int $faqId, int $sortOrder): void
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "aw_faq` SET
            sort_order = '" . (int) $sortOrder . "'
            WHERE faq_id = '" . (int) $faqId . "'");
    }

    public function getAllFaqsForExport(): array
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "aw_faq` ORDER BY sort_order ASC");

        $faqs = [];

        foreach ($query->rows as $faq) {
            $descQuery = $this->db->query("SELECT * FROM `" . DB_PREFIX . "aw_faq_description`
                WHERE faq_id = '" . (int) $faq['faq_id'] . "'");

            $descriptions = [];

            foreach ($descQuery->rows as $desc) {
                $descriptions[$desc['language_id']] = [
                    'question' => $desc['question'],
                    'answer'   => $desc['answer'],
                ];
            }

            $faqs[] = [
                'faq_id'       => (int) $faq['faq_id'],
                'sort_order'   => (int) $faq['sort_order'],
                'status'       => (int) $faq['status'],
                'descriptions' => $descriptions,
            ];
        }

        return $faqs;
    }

    public function truncateAndImportFaqs(array $faqs): void
    {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "aw_faq_description`");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "aw_faq`");

        foreach ($faqs as $faq) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "aw_faq` SET
                faq_id = '" . (int) $faq['faq_id'] . "',
                sort_order = '" . (int) ($faq['sort_order'] ?? 0) . "',
                status = '" . (int) ($faq['status'] ?? 1) . "',
                date_added = NOW()");

            if (isset($faq['descriptions'])) {
                foreach ($faq['descriptions'] as $languageId => $desc) {
                    $this->db->query("INSERT INTO `" . DB_PREFIX . "aw_faq_description` SET
                        faq_id = '" . (int) $faq['faq_id'] . "',
                        language_id = '" . (int) $languageId . "',
                        question = '" . $this->db->escape($desc['question'] ?? '') . "',
                        answer = '" . $this->db->escape($desc['answer'] ?? '') . "'");
                }
            }
        }
    }

    public function deleteOldFaqPage(): void
    {
        // Delete old information page FAQ (id=10)
        $this->db->query("DELETE FROM `" . DB_PREFIX . "information_description`
            WHERE information_id = '10'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "information_to_store`
            WHERE information_id = '10'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "information_to_layout`
            WHERE information_id = '10'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "information`
            WHERE information_id = '10'");

        // Delete SEO URLs for old FAQ page
        $this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url`
            WHERE query = 'information_id=10'");
    }
}
