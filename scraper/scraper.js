const puppeteer = require('puppeteer');
const { executablePath } = require('puppeteer');

(async () => {
  const browser = await puppeteer.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox'],
    executablePath: executablePath()
  });

  const allEvents = [];

  const gooutPage = await browser.newPage();
  await gooutPage.goto('https://goout.rs/', { waitUntil: 'domcontentloaded' });

  await gooutPage.waitForSelector('.MuiTypography-eventTitle', { timeout: 10000 });

  const gooutEvents = await gooutPage.$$eval('a[href*="/event/"]', (elements) => {
    return elements.map((el) => ({
      title: el.querySelector('.MuiTypography-eventTitle')?.textContent.trim() || 'N/A',
      url: el.href
    }));
  });

  const concurrency = 2;
  for (let i = 0; i < gooutEvents.length; i += concurrency) {
    const chunk = gooutEvents.slice(i, i + concurrency);

    const results = await Promise.all(chunk.map(async ({ title, url }) => {
      const eventPage = await browser.newPage();
      try {
        await eventPage.goto(url, { waitUntil: 'networkidle0' });

        const eventData = await eventPage.evaluate(() => {
          let naslov = document.querySelector('h1')?.innerText.trim() || '';
          let lokacija = document.querySelector('h3')?.innerText.trim() || '';
          let tagovi = Array.from(document.querySelectorAll('a.css-f3f42o span')).map(tag => tag.innerText.trim());

          let datum = [...document.querySelectorAll('.css-1el6dq')]
            .find(el => el.querySelector('.MuiTypography-whenAndWhereTitle')?.innerText.trim() === 'Datum')
            ?.querySelector('.MuiTypography-whenAndWhereContent')?.innerText.trim() || '';

          let vreme = [...document.querySelectorAll('.css-1el6dq')]
            .find(el => el.querySelector('.MuiTypography-whenAndWhereTitle')?.innerText.trim() === 'Vreme')
            ?.querySelector('.MuiTypography-whenAndWhereContent')?.innerText.trim() || '';

          let adresa = [...document.querySelectorAll('.css-1el6dq')]
            .find(el => el.querySelector('.MuiTypography-whenAndWhereTitle')?.innerText.trim() === 'Lokacija')
            ?.querySelector('.MuiTypography-whenAndWhereContent')?.innerText.trim() || '';

          let eventStart = `${datum} ${vreme}`.trim();
          return { event: naslov, place: lokacija, category: tagovi, event_start: eventStart, location: adresa };
        });

        await eventPage.close();
        return eventData;
      } catch (error) {
        console.error(`Greška pri skrejpovanju događaja: ${title} (${url})`);
        console.error(error);
        await eventPage.close();
        return null;
      }
    }));

    allEvents.push(...results.filter(e => e !== null));
  }

  
  const beatPage = await browser.newPage();
  await beatPage.goto('https://belgrade-beat.rs/lat/desavanja/danas', { waitUntil: 'domcontentloaded' });

  await beatPage.waitForSelector('.colx.w-75', { timeout: 10000 });

  const today = new Date();
  const dan = today.getDate();
  const meseci = ['Januar', 'Februar', 'Mart', 'April', 'Maj', 'Jun', 'Jul', 'Avgust', 'Septembar', 'Oktobar', 'Novembar', 'Decembar'];
  const dani = ['Nedelja', 'Ponedeljak', 'Utorak', 'Sreda', 'Četvrtak', 'Petak', 'Subota'];
  const formattedDate = `${dan}. ${meseci[today.getMonth()]} - ${dani[today.getDay()]}`;

  const beatEvents = await beatPage.evaluate((formattedDate) => {
    const eventNodes = document.querySelectorAll('.colx.w-75');
    const scrapedEvents = [];

    eventNodes.forEach(event => {
      const title = event.querySelector('h2')?.innerText.trim() || '';

      const timeDiv = [...event.querySelectorAll('div')]
        .find(div => div.innerText.includes('Vreme:'));
      const time = timeDiv ? timeDiv.innerText.replace('Vreme:', '').trim() : '';

      const locationDiv = [...event.querySelectorAll('div')]
        .find(div => div.innerText.includes('Mesto:'));
      const location = locationDiv ? locationDiv.innerText.replace('Mesto:', '').trim() : '';

      const tags = [...event.querySelectorAll('.mt1 .dib')].map(tag => tag.innerText.trim());

      const eventStart = `${formattedDate} ${time}`.trim();

      scrapedEvents.push({
        event: title,
        place: location,
        category: tags,
        event_start: eventStart,
        location: location,
      });
    });

    return scrapedEvents;
  }, formattedDate);

  allEvents.push(...beatEvents);

  
  console.log(JSON.stringify(allEvents, null, 2));

  await browser.close();
})();
