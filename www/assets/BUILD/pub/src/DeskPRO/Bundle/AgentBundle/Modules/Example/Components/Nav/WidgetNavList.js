import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import { loadTypes } from '../../RecordStores/Actions/widgetTypeActions';
import { setWidgetFilter } from '../../Actions/actions';
import { typesSelector, typesStatus } from '../../RecordStores/Selectors/widgetTypesSelectors';
import { ListSection, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';

@connect(state => ({
  status: typesStatus(state),
  widgetTypes: typesSelector(state),
  navItem: state.Example.app.get('navItem')
}))
export class WidgetNavListContainer extends Component {
  componentDidMount() {
    const { status, dispatch } = this.props;

    if (!status.get('isDone')) {
      dispatch(loadTypes());
    }
  }

  getActiveWidgetId() {
    if (!this.props.navItem || this.props.navItem.get('type') !== 'widgetType') {
      return null;
    }

    return this.props.navItem.getIn(['params', 'widgetType'], null);
  }

  activateWidgetType = (w) => {
    const { dispatch } = this.props;
    dispatch(setWidgetFilter({
      widgetType: w.get('id')
    }));
  };

  render() {
    const { status, widgetTypes } = this.props;

    if (!status.get('isDone') || status.get('isLoading')) {
      return (<div>Loading</div>);
    }

    return (
      <WidgetNavList widgetTypes={widgetTypes} activeId={this.getActiveWidgetId()} onActivate={this.activateWidgetType} />
    );
  }
}

export class WidgetNavList extends Component {

  static propTypes = {
    widgetTypes: PropTypes.object.isRequired,
    onActivate: PropTypes.func.isRequired,
    activeId: PropTypes.string
  };

  constructor(props) {
    super(props);
  }

  isActive(w) {
    console.log("%s === %s", w.get('id'), this.props.activeId);
    return w.get('id') === this.props.activeId;
  }

  render() {
    const { widgetTypes, onActivate } = this.props;

    return (
      <ListSection>
        {widgetTypes.map(w => {
          const clickFn = function(ev) {
            onActivate(w, ev);
          };
          return (
            <ListItem label={w.get('name')} onClick={clickFn} active={this.isActive(w)} />
          )
        })}
      </ListSection>
    );
  }
}
