import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { connect } from 'react-redux';
import { updateRoutingState } from '../../../Application/Actions/routingActions';
import { routingStateSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';

export class TabsPane extends React.Component {

  static propTypes = {
    children: PropTypes.node
  };

  static defaultTab = 0;

  constructor(props) {
    super(props);
    Object.assign(this, { state: { active: TabsPane.defaultTab }, titles: [] });
    Object.assign(this, { tabs: this.tabsFromChildren() });
  }

  activate(index) {
    return event => {
      event.preventDefault();
      this.setState({ active: index });
    };
  }

  tabsFromChildren() {
    const tabs     = [];
    const children = this.props.children.length ? this.props.children : [this.props.children];
    for (let i = 0; i < children.length; i++) {
      if (children[i].type.displayName !== 'Tab') {
        throw new Error('TabsPane can only contain Tab components as first level children');
      }

      this.titles.push(children[i].props.title);

      tabs.push({ index:   i,
                  title:   children[i].props.title,
                  icon:    children[i].props.icon,
                  content: children[i].props.children
                });
    }

    return tabs;
  }

  renderTabHeader({ title, icon, index }) {
    const className = index === this.state.active ? 'active' : '';
    const onClick   = this.activate(index).bind(this);
    const content   = icon
      ? (<span className="icon"><i className={`fa${icon}`} /></span>)
      : title;

    return (<li key={index} className={className}><a href="#" onClick={onClick}>{content}</a></li>);
  }

  render() {
    const className = `tabs sidebar-tabs tabs-${this.tabs.length}`;

    return (
      <div>
        <ul className={className}>
          {this.tabs.map(tab => this.renderTabHeader(tab))}
        </ul>

        {this.tabs.map(tab => {
          const classes = classNames('sidebar-list', { hidden: tab.index !== this.state.active });

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
    state:    PropTypes.object.isRequired,
    id:       PropTypes.string.isRequired
  };

  constructor(props) {
    super(props);
    const activeTabId = this.titles.indexOf(this.props.state.getIn([this.props.id, 'active'], null));
    Object.assign(this, { state: { active: activeTabId > -1 ? activeTabId : TabsPane.defaultTab } });
  }

  activate(index) {
    const parentHandler = super.activate(index);

    return event => {
      this.props.dispatch(updateRoutingState(this.props.id, 'active', this.titles[index]));
      parentHandler(event);
    };
  }
}

export class Tab extends React.Component {
  static displayName = 'Tab';

  render() {
    return null;
  }
}
