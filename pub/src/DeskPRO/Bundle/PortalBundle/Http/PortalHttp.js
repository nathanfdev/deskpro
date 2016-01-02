import $ from 'jquery';
import Http from 'DeskPRO/Component/Http/Http';
import PortalUrlCorrector from './PortalUrlCorrector';

const portalHttp = new Http($.ajax);
portalHttp.addInterceptor(PortalUrlCorrector);

export default portalHttp;
