import EventEmitter from 'eventemitter2';
import store from './store';
import { reloadOptions } from '../Modules/Application/Actions/dpWindowActions';

const emitter = new EventEmitter();
emitter.on('reloadOptions', options => store.dispatch(reloadOptions(options)));

export default emitter;
