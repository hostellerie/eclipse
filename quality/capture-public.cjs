const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const baseUrl = (process.argv[2] || '').replace(/\/$/, '');
const outputRoot = path.resolve(process.argv[3] || path.join(__dirname, 'reference', 'public'));
if (!/^https?:\/\//i.test(baseUrl)) throw new Error('Pass an HTTP(S) base URL.');

const pages = [
  { key: 'home', path: '/' },
  { key: 'article', path: '/article.php/monitor-plugin-1-3-0' },
  { key: 'password', path: '/users.php?mode=getpassword' }
];
const viewports = [
  { key: 'mobile-360', width: 360, height: 800 },
  { key: 'tablet-768', width: 768, height: 1024 },
  { key: 'desktop-1440', width: 1440, height: 1000 }
];

(async () => {
  fs.mkdirSync(outputRoot, { recursive: true });
  const browserOptions = { headless: true };
  if (process.env.ECLIPSE_BROWSER_PATH) browserOptions.executablePath = process.env.ECLIPSE_BROWSER_PATH;
  const browser = await chromium.launch(browserOptions);
  const report = { baseUrl, capturedAt: new Date().toISOString(), results: [] };
  let rateLimited = false;
  for (const viewport of viewports) {
    const context = await browser.newContext({ viewport: { width: viewport.width, height: viewport.height }, colorScheme: 'light', reducedMotion: 'reduce' });
    for (const target of pages) {
      const page = await context.newPage();
      const consoleErrors = [];
      const failedRequests = [];
      page.on('console', message => { if (message.type() === 'error') consoleErrors.push(message.text()); });
      page.on('requestfailed', request => failedRequests.push({ url: request.url(), error: request.failure() ? request.failure().errorText : 'unknown' }));
      const response = await page.goto(baseUrl + target.path, { waitUntil: 'networkidle', timeout: 45000 });
      if (response && response.status() === 429) {
        report.results.push({ page: target.key, viewport: viewport.key, url: page.url(), status: 429, screenshot: null, consoleErrors, failedRequests, stopped: 'rate-limited' });
        await page.close(); rateLimited = true; break;
      }
      const audit = await page.evaluate(() => ({
        title: document.title,
        language: document.documentElement.lang || '',
        headings: Array.from(document.querySelectorAll('h1,h2')).map(node => ({ level: node.tagName, text: node.textContent.trim() })).slice(0, 20),
        missingImages: Array.from(document.images).filter(image => image.complete && image.naturalWidth === 0).map(image => image.currentSrc || image.src),
        horizontalOverflow: Math.max(document.documentElement.scrollWidth, document.body ? document.body.scrollWidth : 0) - document.documentElement.clientWidth,
        skipLink: Boolean(document.querySelector('a[href="#main-content"]')),
        main: Boolean(document.querySelector('main#main-content'))
      }));
      const file = `${target.key}-${viewport.key}.png`;
      await page.screenshot({ path: path.join(outputRoot, file), fullPage: true });
      report.results.push({ page: target.key, viewport: viewport.key, url: page.url(), status: response ? response.status() : null, screenshot: file, consoleErrors, failedRequests, ...audit });
      await page.close();
    }
    await context.close();
    if (rateLimited) break;
  }
  await browser.close();
  fs.writeFileSync(path.join(outputRoot, 'report.json'), JSON.stringify(report, null, 2) + '\n');
  const failures = report.results.filter(result => result.status >= 400 || result.missingImages.length || result.horizontalOverflow > 1 || result.failedRequests.length);
  process.stdout.write(`Captured ${report.results.length} references; ${failures.length} require review.\n`);
  if (failures.length) process.stdout.write(failures.map(item => `${item.page}/${item.viewport}: status=${item.status}, missing=${item.missingImages.length}, overflow=${item.horizontalOverflow}, requests=${item.failedRequests.length}`).join('\n') + '\n');
})().catch(error => { console.error(error); process.exit(1); });
