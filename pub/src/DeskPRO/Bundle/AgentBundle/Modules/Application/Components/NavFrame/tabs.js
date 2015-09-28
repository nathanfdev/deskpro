import React from 'react';
import classNames from 'classnames';

export class TabsPane extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      active: 0
    }
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
          const classes = classNames('sidebar-list', {'hidden': tab.index !== this.state.active});

          return (<div key={tab.index} className={classes}>{tab.content}</div>);
        })}
      </div>
    );
  }

  renderTabHeader({title, icon, index}) {
    const className = index === this.state.active ? 'active' : '';
    const onClick = this.activate(index).bind(this);
    const content = title
                  ? title
                  : (<span className="icon"><i className={'fa ' + icon}></i></span>);

    return (<li key={index} className={className}><a href="#" onClick={onClick}>{content}</a></li>);
  }

  tabsFromChildren() {
    const tabs = [];
    const children = this.props.children.length ? this.props.children : [this.props.children];
    for (let i = 0; i < children.length; i++) {
      if (children[i].type.name !== 'Tab') {
        throw 'TabsPane can only contain Tab components as first level children';
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

  activate(index) {
    return function(e) {
      e.preventDefault();
      this.setState({active: index});
    }
  }
}

export class Tab extends React.Component {
  render() {
    return null;
  }
}