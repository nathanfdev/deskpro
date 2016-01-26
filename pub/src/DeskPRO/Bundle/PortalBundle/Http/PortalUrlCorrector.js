import { UrlCorrector } from 'DeskPRO/Bundle/AppBundle/Http/UrlCorrector';
import { portalUrlGenerator } from './PortalUrlGenerator';

const urlCorrector = new UrlCorrector(portalUrlGenerator.path('/'));

export default urlCorrector;
