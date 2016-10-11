import jQuery from '../../bower_components/jquery/dist/jquery.min';
import { start } from './AdminLoad';

__webpack_public_path__ = window.DP_ASSET_URL + 'app-build/';
jQuery(document).on('ready', start());
