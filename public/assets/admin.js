(function(){
  const type = document.getElementById('eventType');
  const clubPanel = document.querySelector('[data-kind="clubs"]');

  function syncClubPanel(){
    if (!type || !clubPanel) return;
    clubPanel.style.display = type.value === 'clubbing' ? 'block' : 'none';
  }

  syncClubPanel();
  if (type) type.addEventListener('change', syncClubPanel);

  const shortDescription = document.querySelector('[data-counter="shortDescription"]');
  const shortDescriptionCount = document.getElementById('shortDescriptionCount');

  function syncShortCounter(){
    if (!shortDescription || !shortDescriptionCount) return;
    shortDescriptionCount.textContent = shortDescription.value.length;
  }

  syncShortCounter();
  if (shortDescription) shortDescription.addEventListener('input', syncShortCounter);

  const newsExcerpt = document.querySelector('[data-counter="newsExcerpt"]');
  const newsExcerptCount = document.getElementById('newsExcerptCount');
  function syncNewsExcerptCounter(){
    if (!newsExcerpt || !newsExcerptCount) return;
    newsExcerptCount.textContent = newsExcerpt.value.length;
  }
  syncNewsExcerptCounter();
  if (newsExcerpt) newsExcerpt.addEventListener('input', syncNewsExcerptCounter);

  const fanpageSelect = document.querySelector('[data-fanpage-select]');
  const fanpageInput = document.querySelector('[data-fanpage-input]');

  function syncFanpageInput(){
    if (!fanpageSelect || !fanpageInput) return;

    if (fanpageSelect.value && fanpageSelect.value !== 'custom') {
      fanpageInput.value = fanpageSelect.value;
      fanpageInput.readOnly = true;
      fanpageInput.classList.add('is-readonly');
      return;
    }

    fanpageInput.readOnly = false;
    fanpageInput.classList.remove('is-readonly');
    if (fanpageSelect.value === '') {
      fanpageInput.value = '';
    }
  }

  syncFanpageInput();
  if (fanpageSelect) fanpageSelect.addEventListener('change', syncFanpageInput);

  function setVisibleEmailChecks(checked){
    document.querySelectorAll('[data-email-contact]').forEach(function(input){
      input.checked = checked;
    });
  }

  document.addEventListener('click', function(event){
    const selectFacebookEvents = event.target.closest('[data-select-facebook-events]');
    if (selectFacebookEvents) {
      document.querySelectorAll('[data-facebook-event-check]:not(:disabled)').forEach(function(input){
        input.checked = selectFacebookEvents.checked;
      });
      return;
    }

    const openSelectedFacebook = event.target.closest('[data-open-selected-facebook]');
    if (openSelectedFacebook) {
      const urls = Array.from(document.querySelectorAll('[data-facebook-event-check]:checked'))
        .map(function(input){ return input.value; })
        .filter(Boolean);

      if (!urls.length) {
        window.alert('Zaznacz najpierw wydarzenia z linkiem do Facebooka.');
        return;
      }

      urls.forEach(function(url){
        window.open(url, '_blank', 'noopener');
      });
      return;
    }

    const copyButton = event.target.closest('[data-copy-url]');
    if (copyButton) {
      const value = copyButton.getAttribute('data-copy-url') || '';
      if (!value) return;

      const done = function(){
        copyButton.classList.add('is-copied');
        const previous = copyButton.textContent;
        copyButton.textContent = 'OK';
        window.setTimeout(function(){
          copyButton.classList.remove('is-copied');
          copyButton.textContent = previous;
        }, 1200);
      };

      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(value).then(done).catch(function(){});
      } else {
        const input = document.createElement('textarea');
        input.value = value;
        input.setAttribute('readonly', 'readonly');
        input.style.position = 'fixed';
        input.style.opacity = '0';
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        input.remove();
        done();
      }
      return;
    }

    const selectVisibleEmails = event.target.closest('[data-select-visible-emails]');
    if (selectVisibleEmails) {
      setVisibleEmailChecks(true);
      return;
    }

    const removeButton = event.target.closest('[data-remove-row]');
    if (removeButton) {
      const row = removeButton.closest('.mini-grid');
      if (row) row.remove();
      return;
    }

    const button = event.target.closest('[data-add]');
    if (!button) return;

    const kind = button.getAttribute('data-add');
    const assets = window.crmAssetLibraries || { clubImages: [], partnerLogos: [] };
    const optionList = function(items, placeholder){
      return '<option value="">' + placeholder + '</option>' + items.map(function(item){
        const label = item.split('/').pop();
        return '<option value="' + item + '">' + label + '</option>';
      }).join('');
    };

    const templates = {
      club: [
        'clubsRows',
        '<input name="clubs[__i__][name]" placeholder="Nazwa klubu">' +
        '<input name="clubs[__i__][address]" placeholder="Adres">' +
        '<input name="clubs[__i__][map_url]" placeholder="Link do mapy">' +
        '<input name="clubs[__i__][image_url]" placeholder="Logo/zdjecie URL">' +
        '<label class="file-inline">Wgraj logo/zdjecie klubu <input type="file" name="club_uploads[__i__]" accept="image/jpeg,image/png,image/webp,image/gif"></label>' +
        '<select name="clubs[__i__][image_library]">' + optionList(assets.clubImages || [], 'Wybierz z wgranych zdjec klubow') + '</select>' +
        '<textarea name="clubs[__i__][description]" placeholder="Opis klubu"></textarea>' +
        '<button type="button" class="remove-row" data-remove-row>Usun klub</button>'
      ],
      video: [
        'videosRows',
        '<input name="videos[__i__][title]" placeholder="Tytul filmu">' +
        '<input name="videos[__i__][youtube_url]" placeholder="Link YouTube">' +
        '<button type="button" class="remove-row" data-remove-row>Usun film</button>'
      ],
      partner: [
        'partnersRows',
        '<input name="partners[__i__][name]" placeholder="Nazwa partnera">' +
        '<input name="partners[__i__][category]" placeholder="Typ">' +
        '<input name="partners[__i__][logo_url]" placeholder="Logo URL">' +
        '<input name="partners[__i__][website_url]" placeholder="Link">' +
        '<label class="file-inline">Wgraj logo partnera <input type="file" name="partner_logo_uploads[__i__]" accept="image/jpeg,image/png,image/webp,image/gif"></label>' +
        '<select name="partners[__i__][logo_library]">' + optionList(assets.partnerLogos || [], 'Wybierz z wgranych logo partnerow') + '</select>' +
        '<button type="button" class="remove-row" data-remove-row>Usun partnera</button>'
      ]
    };

    const template = templates[kind];
    if (!template) return;

    const wrap = document.getElementById(template[0]);
    const row = document.createElement('div');
    row.className = kind === 'video' ? 'mini-grid two' : 'mini-grid';
    row.innerHTML = template[1].replaceAll('__i__', wrap.children.length);
    wrap.appendChild(row);
  });

  const selectAllEmails = document.querySelector('[data-select-all-emails]');
  if (selectAllEmails) {
    selectAllEmails.addEventListener('change', function(){
      setVisibleEmailChecks(selectAllEmails.checked);
    });
  }
})();
