<?php
use PHPUnit\Framework\TestCase;

final class AuditAndGdprTest extends TestCase
{
    public function testAuditLogsPageLoads()
    {
        $this->assertTrue(file_exists(__DIR__ . '/../../admin/audit_logs.php'));
    }

    public function testGdprAjaxExists()
    {
        $this->assertTrue(file_exists(__DIR__ . '/../../admin/ajax/gdpr_actions.php'));
    }

    public function testPrivacyAjaxExists()
    {
        $this->assertTrue(file_exists(__DIR__ . '/../../admin/ajax/privacy_actions.php'));
    }
}


