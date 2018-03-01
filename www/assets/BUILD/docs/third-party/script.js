function run() {
  var licData = [];

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

    customInfo.php.forEach(function(item) {
      if (typeof item.license === 'string') {
        item.license = [item.license];
      }

      licData.push({
        name: item.name,
        link: item.link || null,
        license: item.license,
        managedVia: "manual"
      });
    });
    customInfo.javascript.forEach(function(item) {
      if (typeof item.license === 'string') {
        item.license = [item.license];
      }
      licData.push({
        name: item.name,
        link: item.link || null,
        license: item.license,
        managedVia: "manual"
      });
    });

    bowerInfo.bower.forEach(function(item) {
      if (typeof item.license === 'string') {
        item.license = [item.license];
      }
      licData.push({
        name: item.name,
        link: item.link || null,
        license: item.license,
        managedVia: "bower"
      });
    });

    Object.keys(composerInfo.dependencies).forEach(function(name) {
      var item = composerInfo.dependencies[name];
      item.name = name;
      item.link = 'https://packagist.org/packages/' + name;
      item.license = item.license || [];
      if (typeof item.license === 'string') {
        item.license = [item.license];
      }

      licData.push({
        name: item.name,
        link: item.link || null,
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

      if (item.url) {
        item.link = item.url;
      } else if (item.repository) {
        item.link = item.repository;
      } else {
        item.link = 'https://www.npmjs.com/package/' + name;
      }

      item.license = item.licenses || [];
      if (typeof item.license === 'string') {
        item.license = [item.license];
      }

      item.license = item.license.map(function(l) {
        if (l.indexOf('Custom: ') !== -1) {
          return 'Custom';
        } else {
          return l;
        }
      });

      licData.push({
        name: item.name,
        link: item.link || null,
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

      if (item.url) {
        item.link = item.url;
      } else if (item.repository) {
        item.link = item.repository;
      } else {
        item.link = 'https://www.npmjs.com/package/' + name;
      }

      item.license = item.licenses || [];
      if (typeof item.license === 'string') {
        item.license = [item.license];
      }

      item.license = item.license.map(function(l) {
        if (l.indexOf('Custom: ') !== -1) {
          return 'Custom';
        } else {
          return l;
        }
      });

      licData.push({
        name: item.name,
        link: item.link || null,
        license: item.license,
        managedVia: "npm-legacy"
      });
    });

    licData.sort(function(a, b) {
      return a.name.replace(/^@/, '').localeCompare(b.name.replace(/^@/, ''));
    });

    var haveNames = [];
    licData = licData.filter(function(i) {
      if (haveNames.indexOf(i.name) !== -1) {
        return false;
      }
      haveNames.push(i.name);
      return true;
    });

    var source   = document.getElementById('licListTpl').innerHTML;
    var template = Handlebars.compile(source);
    var html     = template({ licData: licData });

    $('#licList').html(html);

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