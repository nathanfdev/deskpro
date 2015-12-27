import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { updateRoutingState } from '../../../Application/Actions/routingActions';
import { connect } from 'react-redux';
import { routingStateSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';

export class TabsPane extends React.Component {

  static propTypes = {
    children: PropTypes.node
  };

  constructor(props) {
    super(props);
    this.state = {
      active: TabsPane.defaultTab
    };
  }

  static defaultTab = 0;

  activate(index) {
    return event => {
      event.preventDefault();
      this.setState({ active: index });
    };
  }

  tabsFromChildren() {
    const tabs = [];
    const children = this.props.children.length ? this.props.children : [this.props.children];
    for (let i = 0; i < children.length; i++) {
      if (children[i].type.name !== 'Tab') {
        throw new Error('TabsPane can only contain Tab components as first level children');
      }

      tabs.push({
        index: i,
        title: children[i].props.title,
        icon: children[i].props.icon,
        content: children[i].props.children
      });
    }

    return tabs;
  }

  renderTabHeader({title, icon, index}) {
    const className = index === this.state.active ? 'active' : '';
    const onClick = this.activate(index).bind(this);
    const content = title
      ? title
      : (<span className="icon"><i className={'fa ' + icon}></i></span>);

    return (<li key={index} className={className}><a href="#" onClick={onClick}>{content}</a></li>);
  }

  render() {
    const tabs = this.tabsFromChildren();
    const className = 'tabs sidebar-tabs tabs-' + tabs.length;

    return (
      <div>
        <ul className={className}>
          {tabs.map(tab => this.renderTabHeader(tab))}
        </ul>

        {tabs.map(tab => {
          const classes = classNames('sidebar-list', { 'hidden': tab.index !== this.state.active });

          return (<div key={tab.index} className={classes}>{tab.content}</div>);
        })}
      </div>
    );
  }
}

@connect(state => ({ state: routingStateSelector(state) }))
export class TabsPaneStatefulContainer extends TabsPane {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    state: PropTypes.object.isRequired,
    id: PropTypes.string.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      active: this.props.state.getIn([this.props.id, 'active'], TabsPane.defaultTab)
    };
  }

  activate(index) {
    const parentHandler = super.activate(index);

    return event => {
      this.props.dispatch(updateRoutingState(this.props.id, 'active', index));
      parentHandler(event);
    };
  }
}

export class Tab extends React.Component {
  render() {
    return null;
  }
}
