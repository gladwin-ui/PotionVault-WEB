import { test, expect } from '@playwright/test';

const accounts = {
    admin: { email: 'admin@potionvault.test', password: 'password' },
    customer: { email: 'aria@potionvault.test', password: 'password' },
};

async function login(page, account) {
    await page.goto('/login');
    await page.locator('input[name="email"]').fill(account.email);
    await page.locator('input[name="password"]').fill(account.password);
    await page.getByRole('button', { name: 'Masuk ke Vault' }).click();
}

async function assertResponsive(page, path) {
    const metrics = await page.evaluate(() => ({
        width: window.innerWidth,
        scrollWidth: document.documentElement.scrollWidth,
        bodyWidth: document.body.scrollWidth,
        offenders: [...document.querySelectorAll('body *')].map((el) => ({ tag: el.tagName, cls: el.className, right: Math.round(el.getBoundingClientRect().right), width: Math.round(el.getBoundingClientRect().width) })).filter((item) => item.right > window.innerWidth + 1).slice(0, 8),
    }));
    if (metrics.scrollWidth > metrics.width + 1) console.log(`responsive overflow ${path}`, metrics);
    await expect(metrics.scrollWidth, `${path}: unexpected horizontal overflow ${JSON.stringify(metrics)}`).toBeLessThanOrEqual(metrics.width + 1);
    await expect(page.locator('body')).toBeVisible();
    const visibleButtons = page.locator('button:visible');
    if (await visibleButtons.count()) await expect(visibleButtons.first()).toBeVisible();
}

for (const viewport of [
    { name: 'desktop', width: 1440, height: 900 },
    { name: 'tablet', width: 768, height: 1024 },
    { name: 'mobile', width: 390, height: 844 },
]) {
    test.describe(`${viewport.name} responsive`, () => {
        test.use({ viewport: { width: viewport.width, height: viewport.height } });

        test('customer pages remain usable', async ({ page }) => {
            await login(page, accounts.customer);
            for (const path of ['/dashboard', '/catalog', '/catalog/1', '/rental-request?unit_id=1', '/my-rentals', '/billing']) {
                await page.goto(path);
                await assertResponsive(page, path);
            }
        });

        test('admin pages remain usable', async ({ page }) => {
            await login(page, accounts.admin);
            for (const path of ['/admin', '/admin/artifacts', '/admin/categories', '/admin/reports']) {
                await page.goto(path);
                await assertResponsive(page, path);
            }
        });
    });
}
