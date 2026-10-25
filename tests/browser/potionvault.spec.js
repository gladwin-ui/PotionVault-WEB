import { test, expect } from '@playwright/test';

const admin = { email: 'admin@potionvault.test', password: 'password' };
const customer = { email: 'aria@potionvault.test', password: 'password' };

async function login(page, account) {
    await page.goto('/login');
    await page.locator('input[name="email"]').fill(account.email);
    await page.locator('input[name="password"]').fill(account.password);
    await page.getByRole('button', { name: 'Masuk ke Vault' }).click();
}

test.describe.serial('PotionVault browser compliance', () => {
    test('public catalog search and rarity/category filters work', async ({ page }) => {
        await page.goto('/catalog');
        await expect(page.getByRole('heading', { name: 'Jelajahi Artefak' })).toBeVisible();
        await page.getByPlaceholder(/Cari ramuan/).fill('Phoenix');
        await page.getByRole('button', { name: 'Cari' }).click();
        await expect(page.getByText('Phoenix Flame Bottle')).toBeVisible();
        await page.selectOption('select[name="rarity"]', 'Legendary');
        await page.getByRole('button', { name: 'Cari' }).click();
        await expect(page.getByText('Phoenix Flame Bottle')).toBeVisible();
        await expect(page.getByText('PHNX-001', { exact: true })).toBeVisible();
        await page.getByRole('link', { name: 'Lihat Detail' }).click();
        await expect(page.getByText('Unit code')).toBeVisible();
        await expect(page.getByText('PHNX-001', { exact: true })).toBeVisible();
    });

    test('customer registration, login, profile update, and logout work', async ({ page }) => {
        const uniqueEmail = `browser-${Date.now()}@potionvault.test`;
        await page.goto('/register');
        await page.locator('input[name="name"]').fill('Browser Customer');
        await page.locator('input[name="email"]').fill(uniqueEmail);
        await page.locator('input[name="phone"]').fill('081234567890');
        await page.locator('input[name="password"]').fill('browser-password');
        await page.locator('input[name="password_confirmation"]').fill('browser-password');
        await page.getByRole('button', { name: 'Buat Akun Customer' }).click();
        await expect(page).toHaveURL(/dashboard/);
        await expect(page.getByText('Browser Customer', { exact: true })).toBeVisible();

        await page.getByRole('link', { name: 'Profil' }).click();
        await page.locator('input[name="name"]').first().fill('Browser Customer Updated');
        await page.getByRole('button', { name: 'Simpan Perubahan' }).click();
        await expect(page.getByText('Profil berhasil diperbarui.')).toBeVisible();

        await page.getByRole('button', { name: 'Keluar' }).click();
        await expect(page).toHaveURL(/127\.0\.0\.1:8001\/$/);
    });

    test('customer can submit and cancel a pending rental', async ({ page }) => {
        await login(page, customer);
        await page.goto('/rental-request?unit_id=1');
        await page.getByRole('checkbox', { name: /Pilih/ }).check();
        await page.getByRole('button', { name: 'Kirim Pengajuan Rental' }).click();
        await expect(page).toHaveURL(/my-rentals/);
        await expect(page.locator('span.badge').filter({ hasText: /^PENDING$/ })).toBeVisible();
        await page.locator('summary').filter({ hasText: 'Cancel' }).click();
        await page.getByRole('button', { name: 'Yes, cancel' }).click();
        await expect(page.getByText('Pengajuan rental berhasil dibatalkan.')).toBeVisible();
        await expect(page.locator('span.badge').filter({ hasText: /^CANCELLED$/ }).first()).toBeVisible();
    });

    test('customer cannot access admin pages', async ({ page }) => {
        await login(page, customer);
        const response = await page.goto('/admin');
        expect(response?.status()).toBe(403);
    });

    test('admin can manage catalog and customer pages', async ({ page }) => {
        await login(page, admin);
        await page.goto('/admin');
        await expect(page.getByRole('heading', { name: 'Kelola Koleksi PotionVault' })).toBeVisible();
        await expect(page.locator('#rentalTrendChart')).toBeVisible();
        await expect(page.locator('#rarityDistributionChart')).toBeVisible();

        await page.goto('/admin/artifacts');
        await expect(page.getByRole('heading', { name: 'Manajemen Artefak' })).toBeVisible();
        await page.getByRole('link', { name: '+ Tambah Artefak' }).click();
        await page.locator('input[name="name"]').fill('Browser Test Artifact');
        await page.locator('input[name="unit_code"]').fill(`BROW-${Date.now()}`);
        await page.locator('textarea[name="description"]').fill('Artifact created by browser compliance test.');
        await page.locator('select[name="rarity"]').selectOption('Rare');
        await page.locator('input[name="rental_price"]').fill('12000');
        await page.locator('select[name="condition"]').selectOption('GOOD');
        await page.getByRole('checkbox').first().check();
        await page.getByRole('button', { name: 'Simpan' }).click();
        await expect(page.getByText('Artefak berhasil ditambahkan.')).toBeVisible();

        await page.goto('/admin/categories');
        await expect(page.getByRole('heading', { name: 'Manajemen Kategori' })).toBeVisible();
        await page.locator('section').filter({ hasText: 'Tambah Kategori' }).first().locator('input[name="name"]').fill(`Browser Category ${Date.now()}`);
        await page.getByRole('button', { name: 'Tambah Kategori' }).click();
        await expect(page.getByText('Kategori berhasil ditambahkan.')).toBeVisible();

        await page.goto('/admin/customers');
        await expect(page.getByRole('heading', { name: 'Manajemen Customer' })).toBeVisible();
    });

    test('admin can approve, confirm payment, hand over, and view report', async ({ page }) => {
        await login(page, customer);
        await page.goto('/rental-request?unit_id=2');
        await page.getByRole('checkbox', { name: /Pilih/ }).check();
        await page.getByRole('button', { name: 'Kirim Pengajuan Rental' }).click();
        await expect(page).toHaveURL(/my-rentals/);

        await page.getByRole('button', { name: 'Keluar' }).click();
        await login(page, admin);
        await page.goto('/admin/requests');
        const requestRow = page.locator('tr').filter({ hasText: 'Aria Willowbrook' }).filter({ hasText: 'Invisibility Flask' });
        await requestRow.getByRole('button', { name: 'Setujui' }).click();
        await expect(page.getByText('Rental berhasil disetujui.')).toBeVisible();
        await page.reload();
        await requestRow.getByRole('button', { name: 'Konfirmasi pembayaran' }).click();
        await expect(page.getByText('Pembayaran berhasil dikonfirmasi.')).toBeVisible();
        await page.reload();
        await requestRow.getByRole('button', { name: 'Serah terima' }).click();
        await expect(page.getByText('Serah terima berhasil dikonfirmasi.')).toBeVisible();

        await page.goto('/admin/returns');
        const returnRow = page.locator('tr').filter({ hasText: 'Invisibility Flask' });
        await returnRow.getByRole('button', { name: 'Konfirmasi pengembalian' }).click();
        await expect(page.getByText('Pengembalian berhasil dikonfirmasi.')).toBeVisible();

        await page.goto('/admin/reports');
        await expect(page.getByRole('heading', { name: 'Laporan' })).toBeVisible();
        await expect(page.getByRole('button', { name: 'Cetak Laporan' })).toBeVisible();
        await page.getByRole('button', { name: 'Terapkan Filter' }).click();
        await expect(page.getByRole('cell', { name: /Aria Willowbrook/ }).first()).toBeVisible();
    });

    test('admin persists multiple categories and rejects duplicate unit codes', async ({ page }) => {
        await login(page, admin);
        await page.goto('/admin/categories');
        const categoryA = `Browser Multi A ${Date.now()}`;
        const categoryB = `Browser Multi B ${Date.now()}`;
        for (const name of [categoryA, categoryB]) {
            await page.locator('input[name="name"]').first().fill(name);
            await page.getByRole('button', { name: 'Tambah Kategori' }).click();
            await expect(page.getByText('Kategori berhasil ditambahkan.')).toBeVisible();
        }

        await page.goto('/admin/artifacts/create');
        const code = `MULTI-${Date.now()}`;
        await page.locator('input[name="name"]').fill('Multi Category Browser Artifact');
        await page.locator('input[name="unit_code"]').fill(code);
        await page.locator('textarea[name="description"]').fill('Browser multi-category coverage.');
        await page.locator('input[name="rental_price"]').fill('12000');
        const categoryChecks = page.locator('input[name="category_ids[]"]');
        await categoryChecks.nth(await categoryChecks.count() - 2).check();
        await categoryChecks.nth(await categoryChecks.count() - 1).check();
        await page.getByRole('button', { name: 'Simpan' }).click();
        await expect(page.getByText('Artefak berhasil ditambahkan.')).toBeVisible();
        await page.goto('/admin/artifacts');
        await page.locator('tr').filter({ hasText: code }).getByRole('link', { name: 'Edit' }).click();
        await expect(page.locator('input[name="category_ids[]"]:checked')).toHaveCount(2);

        await page.goto('/admin/artifacts/create');
        await page.locator('input[name="name"]').fill('Duplicate Code Browser Artifact');
        await page.locator('input[name="unit_code"]').fill(code);
        await page.locator('textarea[name="description"]').fill('Duplicate code must be rejected.');
        await page.locator('input[name="rental_price"]').fill('12000');
        await page.locator('input[name="category_ids[]"]').first().check();
        await page.getByRole('button', { name: 'Simpan' }).click();
        await expect(page.locator('.notice')).toContainText(/unit_code|sudah digunakan|unique|already been taken/i);
        await expect(page).toHaveURL(/admin\/artifacts\/create/);
    });

    test('admin uploads, replaces, and removes an artifact image visible to customers', async ({ page }) => {
        const png = Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', 'base64');
        const name = `Browser Image Artifact ${Date.now()}`;
        const code = `IMG-${Date.now()}`;
        await login(page, admin);
        await page.goto('/admin/artifacts/create');
        await page.locator('input[name="name"]').fill(name);
        await page.locator('input[name="unit_code"]').fill(code);
        await page.locator('textarea[name="description"]').fill('Artifact image browser coverage.');
        await page.locator('input[name="rental_price"]').fill('15000');
        await page.locator('input[name="category_ids[]"]').first().check();
        await page.locator('input[name="image"]').setInputFiles({ name: 'artifact.png', mimeType: 'image/png', buffer: png });
        await expect(page.locator('[data-image-preview]')).toBeVisible();
        await page.getByRole('button', { name: 'Simpan' }).click();
        await expect(page.getByText('Artefak berhasil ditambahkan.')).toBeVisible();
        const row = page.locator('tr').filter({ hasText: code });
        await expect(row.locator('img')).toHaveAttribute('alt', name);

        await row.getByRole('link', { name: 'Edit' }).click();
        await page.locator('input[name="image"]').setInputFiles({ name: 'replacement.png', mimeType: 'image/png', buffer: png });
        await expect(page.locator('[data-image-preview]')).toBeVisible();
        await page.getByRole('button', { name: 'Simpan' }).click();
        await expect(page.getByText('Artefak berhasil diperbarui.')).toBeVisible();

        await page.getByRole('button', { name: 'Keluar' }).click();
        await login(page, customer);
        await page.goto('/catalog');
        await expect(page.locator(`img[alt="${name}"]`)).toBeVisible();
        await page.locator('article').filter({ hasText: name }).getByRole('link', { name: 'Lihat Detail' }).click();
        await expect(page.locator(`img[alt="${name}"]`)).toBeVisible();

        await page.getByRole('button', { name: 'Keluar' }).click();
        await login(page, admin);
        await page.goto('/admin/artifacts');
        await page.locator('tr').filter({ hasText: code }).getByRole('link', { name: 'Edit' }).click();
        await page.locator('input[name="remove_image"]').check();
        await page.getByRole('button', { name: 'Simpan' }).click();
        await expect(page.getByText('Artefak berhasil diperbarui.')).toBeVisible();
        await expect(page.locator('tr').filter({ hasText: code }).locator('img')).toHaveCount(0);
    });

    test('admin report print handler and print media hide navigation', async ({ page }) => {
        await page.addInitScript(() => {
            window.__printCalls = 0;
            window.print = () => { window.__printCalls += 1; };
        });
        await login(page, admin);
        await page.goto('/admin/reports?artifact=INVS-001&payment_status=PAID');
        await expect(page.locator('input[name="artifact"]')).toHaveValue('INVS-001');
        await expect(page.getByRole('cell', { name: /INVS-001/ }).first()).toBeVisible();
        await page.getByRole('button', { name: 'Cetak Laporan' }).click();
        await expect.poll(() => page.evaluate(() => window.__printCalls)).toBe(1);
        await page.emulateMedia({ media: 'print' });
        await expect(page.locator('.sidebar')).toBeHidden();
        await expect(page.locator('button', { hasText: 'Cetak Laporan' })).toBeHidden();
        await expect(page.locator('.report-page')).toContainText('INVS-001');
    });
});
