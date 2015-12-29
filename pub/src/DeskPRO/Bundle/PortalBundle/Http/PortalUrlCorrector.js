import UrlCorrector from 'DeskPRO/Bundle/AppBundle/Http/UrlCorrector';
import PortalUrlGenerator from 'DeskPRO/Bundle/PortalBundle/Http/PortalUrlGenerator';

const urlCorrector = new UrlCorrector(PortalUrlGenerator.path('/'));

export default urlCorrector;
