const puppeteer = require('puppeteer');

(async () => {
  const browser = await puppeteer.launch({ headless: true });
  const page = await browser.newPage();
  
  await page.goto('https://goout.rs/', { waitUntil: 'domcontentloaded' });

  await page.waitForSelector('.MuiTypography-eventTitle', { timeout: 10000 });

  
  const events = await page.$$eval('a[href*="/event/"]', (elements) => {
    return elements.map((el) => ({
      title: el.querySelector('.MuiTypography-eventTitle')?.textContent.trim() || 'N/A',
      url: el.href
    }));
  });

  console.log('Pronađeno događaja:', events.length);
  
  const concurrency = 5;
  let eventsData = [];

  for (let i = 0; i < events.length; i += concurrency) {
    const chunk = events.slice(i, i + concurrency);

    const results = await Promise.all(chunk.map(async ({ title, url }) => {
      console.log(`Obrada događaja: ${title} (${url})`);

      const eventPage = await browser.newPage();
      await eventPage.goto(url, { waitUntil: 'domcontentloaded' });

      try {
       
        await eventPage.goto(url, { waitUntil: 'networkidle0' });

        const eventData = await eventPage.evaluate(() => {
          let naslov = document.querySelector('h1')?.innerText.trim() || 'N/A';
          let lokacija = document.querySelector('h3')?.innerText.trim() || 'N/A';
          let tagovi = Array.from(document.querySelectorAll('a.css-f3f42o span')).map(tag => tag.innerText.trim());

          let datum = [...document.querySelectorAll('.css-1el6dq')]
            .find(el => el.querySelector('.MuiTypography-whenAndWhereTitle')?.innerText.trim() === 'Datum')
            ?.querySelector('.MuiTypography-whenAndWhereContent')?.innerText.trim() || 'N/A';

          let vreme = [...document.querySelectorAll('.css-1el6dq')]
            .find(el => el.querySelector('.MuiTypography-whenAndWhereTitle')?.innerText.trim() === 'Vreme')
            ?.querySelector('.MuiTypography-whenAndWhereContent')?.innerText.trim() || 'N/A';

          let adresa = [...document.querySelectorAll('.css-1el6dq')]
            .find(el => el.querySelector('.MuiTypography-whenAndWhereTitle')?.innerText.trim() === 'Lokacija')
            ?.querySelector('.MuiTypography-whenAndWhereContent')?.innerText.trim() || 'N/A';

          return { event: naslov, place: lokacija, category: tagovi, date: datum, event_start: vreme, location: adresa };
        });

        await eventPage.close();
        return eventData;
      } catch (error) {
        console.error(`⚠️ Greška pri skrejpovanju događaja: ${title} (${url})`);
        console.error(error);
        await eventPage.close();
        return null; 
      }
    }));

    eventsData.push(...results.filter(event => event !== null));
  }

  console.log('Konačni podaci:', eventsData);
  await browser.close();
})();