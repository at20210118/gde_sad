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
  await gooutPage.goto('https://goout.rs/', { waitUntil: 'domcontentloaded', timeout: 60000 });
  await gooutPage.waitForSelector('.MuiTypography-eventTitle', { timeout: 0 });

  const gooutEvents = await gooutPage.$$eval('a[href*="/event/"]', (elements) => {
    return elements.map((el) => ({
      title: el.querySelector('.MuiTypography-eventTitle')?.textContent.trim() || 'N/A',
      url: el.href
    }));
  });

  const page = await browser.newPage();

  for (let i = 0; i < gooutEvents.length; i++) {
    const { title, url } = gooutEvents[i];
    try {
      await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 20000 });

      await page.waitForSelector('h1', { timeout: 10000 });
      await page.waitForSelector('.MuiTypography-whenAndWhereTitle', { timeout: 10000 });

      const eventData = await page.evaluate(() => {
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

        let imageEl = document.querySelector('#eventImage');
        let image = "";
        if (imageEl) {
          const rawSrc = imageEl.getAttribute('src');
          const match = rawSrc.match(/url=(.*?)&/);
          image = match && match[1] ? decodeURIComponent(match[1]) : rawSrc;
        }

        let eventStart = `${datum} ${vreme}`.trim();
        return {
          event: naslov,
          place: lokacija,
          category: tagovi,
          event_start: eventStart,
          location: adresa,
          image
        };
      });

      allEvents.push(eventData);
    } catch (err) {
      console.error(`Greška pri ${title}:`, err.message);
    }
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

      let location = '';
      if (locationDiv) {
        const firstLink = locationDiv.querySelector('a');
        location = firstLink ? firstLink.innerText.trim() : '';
      }

      const imageElement = event.closest('.colx.w-75')?.previousElementSibling?.querySelector('img.imgx');
      let imagePath = null;
      if (imageElement) {
        imagePath = imageElement.getAttribute('src');
      }

      const tags = [...event.querySelectorAll('.mt1 .dib')].map(tag => tag.innerText.trim());

      const eventStart = `${formattedDate} ${time}`.trim();

      scrapedEvents.push({
        event: title,
        place: location,
        category: tags,
        event_start: eventStart,
        location: location,
        image: "https://belgrade-beat.rs" + imagePath,
      });
    });

    return scrapedEvents;
  }, formattedDate);

  allEvents.push(...beatEvents);

  console.log(JSON.stringify(allEvents, null, 2));

  await browser.close();
})();
