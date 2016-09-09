import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import AccordionPanel from './AccordionPanel';

class Accordion extends React.Component {
  static propTypes = {
    panels: PropTypes.arrayOf(PropTypes.shape({
      title:    PropTypes.string,
      icon:     PropTypes.string,
      elements: PropTypes.arrayOf(PropTypes.object)
    })),
    fluid:    PropTypes.bool,
    onChange: PropTypes.func,
    children: PropTypes.node
  };
  static defaultProps = {
    onChange() {
    }
  };

  constructor(props) {
    super(props);
    this.state = {
      activeKey: [0]
    };
  }

  onClickItem(key) {
    return () => {
      let activeKey = this.state.activeKey;
      activeKey = activeKey[0] === key ? [] : [key];
      this.setActiveKey(activeKey);
    };
  }

  setActiveKey = (activeKey) => {
    if (!('activeKey' in this.props)) {
      this.setState({
        activeKey,
      });
    }
    this.props.onChange(activeKey[0]);
  };

  getPanels() {
    const panels = [];
    const items = this.props.panels;
    const activeKey = this.state.activeKey;
    if (!items) {
      return [];
    }
    Object.keys(items).map((i) => {
      const panel = items[i];
      const key = String(i);
      const active = (i === activeKey[0]);
      const props = {
        key,
        active,
        panel,
        onItemClick: this.onClickItem(key)
      };
      panels.push(<AccordionPanel {...props} />);
      return true;
    });
    return panels;
  }

  renderChildren = () =>
    React.Children.map(this.props.children, (child) => {
      if (child && child.type === AccordionPanel) {
        const key = child.key;
        const active = (key === this.state.activeKey[0]);
        return React.cloneElement(child, {
          active,
          onItemClick: this.onClickItem(key)
        });
      }
      return null;
    });


  render() {
    return (<div className={classNames('ui', 'list', 'styled', 'accordion', { fluid: this.props.fluid })}>
      {this.getPanels()}
      {this.renderChildren()}
    </div>);
  }
}
export default Accordion;
