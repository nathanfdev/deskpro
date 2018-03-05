function run() {
  var licData = [];
  var licIds = [];

  $.ajaxSetup({ cache: false });
  $.when(
    $.getJSON("bower-lic-info.json"),
    $.getJSON("composer-lic-info.json"),
    $.getJSON("custom-lic-info.json"),
    $.getJSON("npm-lic-info.json"),
    $.getJSON("legacy-npm-lic-info.json")
  ).done(function(bowerInfoRes, composerInfoRes, customInfoRes, npmInfoRes, legacyNpmInfoRes) {

    var bowerInfo = bowerInfoRes[0],
      composerInfo = composerInfoRes[0],
      customInfo = customInfoRes[0],
      npmInfo = npmInfoRes[0]
      legacyNpmInfo = legacyNpmInfoRes[0];

    function getLicInfo(item) {
      if (customInfo.overrides[item.name]) {
        item = Object.assign({}, item, customInfo.overrides[item.name]);
      }

      if (typeof item.license === 'string') {
        item.license = [item.license];
      }

      item.license = item.license.map(function(l) {
        switch (l) {
          case 'LGPL-2.1': return 'LGPL-2.1-only';
          case 'LGPL-2.1+': return 'LGPL-2.1-or-later';
          case 'LGPL-3.0': return 'LGPL-3.0-only';
          case 'GPL-3.0+': return 'GPL-3.0-or-later';
          case 'GPL-2.0': return 'GPL-2.0-only';
          case 'GPLv2': return 'GPL-2.0-only';
          case 'Public Domain': return 'Public-Domain';
          case 'PSF': return 'Python-2.0';
        }
        return l;
      });

      if (!item.link || item.link === "") {
        item.link = null;
      }

      return item;
    }

    customInfo.php.forEach(function(item) {
      item = getLicInfo(item);

      licData.push({
        name: item.name,
        link: item.link,
        license: item.license,
        managedVia: "manual"
      });
    });
    customInfo.javascript.forEach(function(item) {
      item = getLicInfo(item);
      licData.push({
        name: item.name,
        link: item.link,
        license: item.license,
        managedVia: "manual"
      });
    });

    bowerInfo.bower.forEach(function(item) {
      item = getLicInfo(item);
      licData.push({
        name: item.name,
        link: item.link,
        license: item.license,
        managedVia: "bower"
      });
    });

    Object.keys(composerInfo.dependencies).forEach(function(name) {
      var item = composerInfo.dependencies[name];
      item.name = name;
      item.link = 'https://packagist.org/packages/' + name;
      item.license = item.license || [];

      item = getLicInfo(item);

      licData.push({
        name: item.name,
        link: item.link,
        license: item.license,
        managedVia: "composer"
      });
    });

    Object.keys(npmInfo).forEach(function(rawName) {
      var item = npmInfo[rawName];

      if (item.private) {
        return;
      }

      var name = rawName.split('@');
      name.pop();
      name = name.join('@');
      item.name = name;

      item.link = 'https://www.npmjs.com/package/' + name;

      item.license = item.licenses || ['NONE'];

      item = getLicInfo(item);

      item.license = item.license.map(function(l) {
        if (l.indexOf('Custom: ') !== -1) {
          return 'NONE';
        } else {
          return l;
        }
      });

      licData.push({
        name: item.name,
        link: item.link,
        license: item.license,
        managedVia: "npm"
      });
    });

    Object.keys(legacyNpmInfo).forEach(function(rawName) {
      var item = legacyNpmInfo[rawName];

      if (item.private) {
        return;
      }

      var name = rawName.split('@');
      name.pop();
      name = name.join('@');
      item.name = name;

      item.link = 'https://www.npmjs.com/package/' + name;

      item.license = item.licenses || [];
      if (typeof item.license === 'string') {
        item.license = [item.license];
      }

      item.license = item.license.map(function(l) {
        if (l.indexOf('Custom: ') !== -1) {
          return 'NONE';
        } else {
          return l;
        }
      });

      item = getLicInfo(item);

      licData.push({
        name: item.name,
        link: item.link,
        license: item.license,
        managedVia: "npm-legacy"
      });
    });

    var haveNames = [];
    licData = licData.filter(function(i) {
      if (haveNames.indexOf(i.name) !== -1) {
        return false;
      }
      haveNames.push(i.name);
      i.license.forEach(function(lid) {
        if (licIds.indexOf(lid) === -1) {
          licIds.push(lid);
        }
      });
      return true;
    });

    licIds.sort();
    licData.sort(function(a, b) {
      return a.name.replace(/^@/, '').localeCompare(b.name.replace(/^@/, ''));
    });

    var html = Handlebars.compile(document.getElementById('licListTpl').innerHTML)({ licData: licData });
    $('#licList').html(html);

    var html = Handlebars.compile(document.getElementById('licIdsTpl').innerHTML)({ licIds: licIds });
    $('#licIds').html(html);

    $('#downloadJson').on('click', function(ev) {
      ev.preventDefault();
      var json = JSON.stringify(licData);
      var blob = new Blob([json], {"type":"application/json;charset=utf-8"});
      saveAs(blob, "lic-data.json");
    });

    $('#downloadCsv').on('click', function(ev) {
      ev.preventDefault();
      var csv = [
        ['name', 'link', 'license', 'managedVia'].join(',')
      ];
      licData.forEach(function(item) {
        csv.push([item.name, item.link, item.license.join(','), item.managedVia]
          .map(function(v) { return '"' + v + '"' })
          .join(','));
      });
      var blob = new Blob([csv.join("\n")], {"type":"text/csv;charset=utf-8"});
      saveAs(blob, "lic-data.csv");
    });
  });

  Handlebars.registerHelper('join', function(val, delimiter, start, end) {
    return [].concat(val).slice(start, end).join(delimiter);
  });
}