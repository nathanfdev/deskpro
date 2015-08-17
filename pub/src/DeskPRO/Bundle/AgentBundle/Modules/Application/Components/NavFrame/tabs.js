import React from 'react';

export class TabPane extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      active: 0
    }
  }

  render() {
    const tabs = this.tabsFromChildren();

    return (
      <div>
        <ul className="tabs sidebar-tabs">
          {tabs.map(tab => {
            const className = tab.index === this.state.active ? 'active' : '';
            const onClick = this.activate(tab.index).bind(this);

            return (<li key={tab.index} className={className}><a href="#" onClick={onClick}>{tab.title}</a></li>);
          })}
        </ul>

        {tabs.map(tab => {
          const className = tab.index === this.state.active ? '' : 'hidden';

          return (<div key={tab.index} className={className}>{tab.content}</div>);
        })}
      </div>
    );
  }

  tabsFromChildren() {
    const tabs = [];
    const children = this.props.children.length ? this.props.children : [this.props.children];
    for (let i = 0; i < children.length; i++) {
      if (children[i].type.name !== 'Tab') {
        throw 'TabPane can only contain Tab components as first level children';
      }

      tabs.push({
        index: i,
        title: children[i].props.title,
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