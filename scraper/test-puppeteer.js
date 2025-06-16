const puppeteer = require('puppeteer');

(async () => {
  try {
    const browser = await puppeteer.launch({
      headless: true,
      args: ['--no-sandbox', '--disable-setuid-sandbox'],
      executablePath: puppeteer.executablePath()  // dodaj ako koristiš v21+
    });

    const page = await browser.newPage();
    await page.goto('https://example.com', { waitUntil: 'domcontentloaded' });

    const title = await page.title();
    console.log('Naslov strane je:', title);

    await browser.close();
  } catch (err) {
    console.error('❌ Greška prilikom pokretanja Puppeteer-a:');
    console.error(err);
  }
})();
