import $ from "jquery";
import Http from "DeskPRO/Component/Http/Http";
import PortalUrlCorrector from "DeskPRO/Bundle/PortalBundle/Http/PortalUrlCorrector";

const portal_http = new Http($.ajax);
portal_http.addInterceptor(PortalUrlCorrector);

export default portal_http;
