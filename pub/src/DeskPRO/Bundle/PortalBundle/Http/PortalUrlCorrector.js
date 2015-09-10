import UrlCorrector from "DeskPRO/Bundle/AppBundle/Http/UrlCorrector";
import PortalUrlGenerator from "DeskPRO/Bundle/PortalBundle/Http/PortalUrlGenerator";

const url_corrector = new UrlCorrector(PortalUrlGenerator.path('/'));

export default url_corrector;
