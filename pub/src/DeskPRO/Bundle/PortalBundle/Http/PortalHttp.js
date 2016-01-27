import $ from 'jquery';
import Http from 'DeskPRO/Component/Http/Http';
import PortalUrlCorrector from './PortalUrlCorrector';

export const portalHttp = new Http($.ajax);
portalHttp.addInterceptor(PortalUrlCorrector);
