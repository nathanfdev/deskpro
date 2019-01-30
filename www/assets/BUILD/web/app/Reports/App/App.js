/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS103: Rewrite code to no longer use __guard__
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'angular',
  'Reports/App/ReportsModule',

  'Reports/App/SetupDataServices',
  'Reports/App/SetupDirectives',
  'DeskPRO/App/SetupLogging',
  'DeskPRO/App/SetupNetwork',
  'Reports/App/SetupRouting',
  'DeskPRO/App/SetupServices',
  'Reports/App/SetupTemplates',
  'Reports/Main/Service/SessionPing',
], function(
  angular,
  ReportsModule,

  SetupDataServices,
  SetupDirectives,
  SetupLogging,
  SetupNetwork,
  SetupRouting,
  SetupServices,
  SetupTemplates,

  Reports_Main_Service_SessionPing
) {

  SetupServices(ReportsModule);
  SetupLogging(ReportsModule);
  SetupDataServices(ReportsModule);
  SetupNetwork(ReportsModule);
  SetupDirectives(ReportsModule);
  SetupTemplates(ReportsModule);
  SetupRouting(ReportsModule);

  ReportsModule.factory('dpHttpSessionInterceptor', ['$q', $q =>
    ({
      responseError(rejection) {
        if ((rejection.status != null) && ((rejection.data != null ? rejection.data.error : undefined) != null) && (rejection.status === 403) && (rejection.data.error === "session_expired")) {
          return window.location = window.DP_BASE_URL + 'agent/login?timeout=1&return=' + encodeURIComponent(window.DP_BASE_URL + 'reports/' + window.location.hash);
        } else {
          return $q.reject(rejection);
        }
      }
    })
  
  ]);
  ReportsModule.config(['$httpProvider', $httpProvider => $httpProvider.interceptors.push('dpHttpSessionInterceptor')
  ]);

  ReportsModule.service('SessionPing', ['Api', Api => new Reports_Main_Service_SessionPing(Api)
  ]);
  ReportsModule.run(['SessionPing', SessionPing =>
    window.setTimeout(() => SessionPing.startInterval()
    , 20000)
  
  ]);

  // IE/Edge Hack http://stackoverflow.com/questions/1481251/what-does-document-domain-document-domain-do
  document.domain = document.domain;

  if (window.parent === window.self) {
    window.location.href = window.DP_BASE_URL + 'agent/#reports:' + (window.location.hash.replace(/^#/, '') || '/');
  } else {
    // open agent links in parent window when clicking in iframe
    $(document).on('click', e => {
      const href = $(e.target).attr('href');
      if (!href || (0 !== href.indexOf('/agent/#'))) { return; }
      e.preventDefault();
      const event = new CustomEvent('dpHashChange', { detail: { hash: href.replace(/.+(#.+)/, "$1") } } );
      return window.parent.document.dispatchEvent(event);
    });
  }

  try {
    if (__guard__(window.parent != null ? window.parent.DP_FRAME_OVERLAYS : undefined, x => x.reports)) {
      ReportsModule.run(['$rootScope', $rootScope =>
        $rootScope.$on('$stateChangeSuccess', function() {
          if (window.parent.DP_FRAME_OVERLAYS.reports.opened) {
            return window.parent.DP_FRAME_OVERLAYS.reports.setHash(window.location.hash);
          }
        })
      
      ]);
    }
  } catch (error) {
    const e = error;
    console.log(e);
  }

  return ReportsModule;
});

function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}