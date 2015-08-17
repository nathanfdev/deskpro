import { Reducer } from 'Ampliflux/reducers';
import * as actions from '../Actions/crmNavActions';

export default class CrmNav extends Reducer {
  getInitialState() {
    return {
      test: 'test'
    };
  }
}
