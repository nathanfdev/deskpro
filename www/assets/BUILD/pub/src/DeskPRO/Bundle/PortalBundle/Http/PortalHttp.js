import $ from 'jquery';
import { Http } from 'DeskPRO/Component/Http/Http';
import { portalUrlCorrector } from './PortalUrlCorrector';

export const portalHttp = new Http($.ajax);
portalHttp.addInterceptor(portalUrlCorrector);
