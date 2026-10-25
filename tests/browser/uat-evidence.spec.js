import { test, expect } from '@playwright/test';

const admin = { email: 'admin@potionvault.test', password: 'password' };
const customer = { email: 'aria@potionvault.test', password: 'password' };
const evidence = (name) => `docs/uat/evidence/${name}`;

async function login(page, account) {
    await page.goto('/login');
    await page.locator('input[name="email"]').fill(account.email);
    await page.locator('input[name="password"]').fill(account.password);
    await page.getByRole('button', { name: 'Masuk ke Vault' }).click();
}

test.describe.serial('PotionVault formal UAT evidence', () => {
    test('UAT-AUTH-01/02/03/04 registration login admin and logout', async ({ page }) => {
        const email = `uat-${Date.now()}@potionvault.test`;
        await page.goto('/register');
        await page.locator('input[name="name"]').fill('UAT Customer');
        await page.locator('input[name="email"]').fill(email);
        await page.locator('input[name="phone"]').fill('081234567890');
        await page.locator('input[name="password"]').fill('uat-password');
        await page.locator('input[name="password_confirmation"]').fill('uat-password');
        await page.getByRole('button', { name: 'Buat Akun Customer' }).click();
        await expect(page).toHaveURL(/dashboard/);
        await page.screenshot({ path: evidence('auth/UAT-AUTH-01-registration-dashboard.png'), fullPage: true });
        await page.getByRole('button', { name: 'Keluar' }).click();
        await login(page, admin);
        await expect(page).toHaveURL(/admin/);
        await page.screenshot({ path: evidence('auth/UAT-AUTH-03-admin-dashboard.png'), fullPage: true });
        await page.getByRole('button', { name: 'Keluar' }).click();
        await expect(page).toHaveURL(/127\.0\.0\.1:8001\/$/);
    });

    test('UAT-CAT-01/02/03/04/05 catalog search filter detail and image area', async ({ page }) => {
        await page.goto('/catalog');
        await expect(page.getByRole('heading', { name: 'Jelajahi Artefak' })).toBeVisible();
        await page.screenshot({ path: evidence('customer/UAT-CAT-01-catalog.png'), fullPage: true });
        await page.getByPlaceholder(/Cari ramuan/).fill('Phoenix');
        await page.getByRole('button', { name: 'Cari' }).click();
        await page.selectOption('select[name="rarity"]', 'Legendary');
        await page.getByRole('button', { name: 'Cari' }).click();
        await expect(page.getByText('Phoenix Flame Bottle')).toBeVisible();
        await page.screenshot({ path: evidence('customer/UAT-CAT-02-search-rarity-filter.png'), fullPage: true });
        await page.getByRole('link', { name: 'Lihat Detail' }).click();
        await expect(page.getByText('PHNX-001', { exact: true })).toBeVisible();
        await page.screenshot({ path: evidence('customer/UAT-CAT-04-artifact-detail.png'), fullPage: true });
    });

    test('UAT-REP-01/02 admin dashboard and UAT-ART-01 artifact page', async ({ page }) => {
        await login(page, admin);
        await page.goto('/admin');
        await expect(page.locator('#rentalTrendChart')).toBeVisible();
        await expect(page.locator('#rarityDistributionChart')).toBeVisible();
        await page.screenshot({ path: evidence('admin/UAT-REP-01-02-admin-dashboard-charts.png'), fullPage: true });
        await page.goto('/admin/artifacts');
        await expect(page.getByRole('heading', { name: 'Manajemen Artefak' })).toBeVisible();
        await page.screenshot({ path: evidence('admin/UAT-ART-01-artifact-management.png'), fullPage: true });
    });

    test('UAT-REN-01/05 and UAT-WFL-01/03/04 rental workflow screens', async ({ page }) => {
        await login(page, customer);
        await page.goto('/rental-request?unit_id=1');
        await page.getByRole('checkbox', { name: /Pilih/ }).check();
        await page.getByRole('button', { name: 'Kirim Pengajuan Rental' }).click();
        await expect(page).toHaveURL(/my-rentals/);
        await page.screenshot({ path: evidence('rental/UAT-REN-01-pending-rental.png'), fullPage: true });
        await page.getByRole('button', { name: 'Keluar' }).click();
        await login(page, admin);
        await page.goto('/admin/requests');
        const row = page.locator('tr').filter({ hasText: 'Healing Potion Vessel' }).filter({ hasText: 'Aria Willowbrook' });
        await row.getByRole('button', { name: 'Setujui' }).click();
        await page.reload();
        await row.getByRole('button', { name: 'Konfirmasi pembayaran' }).click();
        await page.reload();
        await row.getByRole('button', { name: 'Serah terima' }).click();
        await expect(page.getByText('Serah terima berhasil dikonfirmasi.')).toBeVisible();
        await page.screenshot({ path: evidence('rental/UAT-WFL-01-03-04-approved-paid-handover.png'), fullPage: true });
    });

    test('UAT-VAL-03/04/05/06 responsive evidence', async ({ page }) => {
        await page.setViewportSize({ width: 390, height: 844 });
        await page.goto('/catalog');
        const metrics = await page.evaluate(() => ({ width: innerWidth, scrollWidth: document.documentElement.scrollWidth }));
        expect(metrics.scrollWidth).toBeLessThanOrEqual(metrics.width + 1);
        await page.screenshot({ path: evidence('responsive/UAT-VAL-05-mobile-catalog.png'), fullPage: true });
    });
});
