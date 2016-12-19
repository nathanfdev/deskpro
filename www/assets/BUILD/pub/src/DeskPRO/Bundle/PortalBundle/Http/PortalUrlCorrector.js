import { UrlCorrector } from 'DeskPRO/Bundle/AppBundle/DAL/Http/UrlCorrector';
import { portalUrlGenerator } from './PortalUrlGenerator';

export const portalUrlCorrector = new UrlCorrector(portalUrlGenerator.path('/'));
