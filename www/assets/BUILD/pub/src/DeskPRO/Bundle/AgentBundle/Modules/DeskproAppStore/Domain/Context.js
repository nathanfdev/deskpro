import Immutable from 'immutable';

export class Context
{
  constructor({ id, type, entityId, locationId, tabId }) {
    const props = { id, type, entityId, locationId, tabId };
    this.props = Immutable.fromJS(props);
  }

  get id() { return this.props.get('id'); }

  get type() { return this.props.get('type'); }

  get entityId() { return this.props.get('entityId'); }

  get locationId() { return this.props.get('locationId'); }

  get tabId() { return this.props.get('tabId'); }

  toJS = () => this.props.toJS();
}
