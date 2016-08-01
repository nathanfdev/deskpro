import React, { PropTypes } from 'react';
import classNames from 'classnames';

class AccordionPanel extends React.Component {
  static propTypes = {
    panel:  PropTypes.shape({
      title: PropTypes.string.isRequired,
      content: PropTypes.oneOfType([
        PropTypes.string,
        PropTypes.node
      ]).isRequired,
      count: PropTypes.arrayOf(PropTypes.any),
      icon: PropTypes.string
    }),
    active: PropTypes.bool,
    onItemClick: PropTypes.func
  };
  static defaultProps = {
    active: false,
    onItemClick()
    {
    }
  };
  constructor(props) {
    super(props);
  }

  handleItemClick() {
    this.props.onItemClick();
  }

  getTitle() {
    let {icon, title, count} = this.props.panel;
    title = [title];
    if (count) {
      title.push(<span className="count"> ({count.length})</span>)
    }
    if (icon) {
      title.unshift(<i className={classNames('icon', icon)}/>);
    }
    return title;
  }

  render() {
    const {panel, active} = this.props;
    return <div>
      <div
        className={classNames('title', {active: active})}
        onClick={this.handleItemClick.bind(this)}
      >
        <i className="dropdown icon"/>
        {this.getTitle()}
      </div>
      <div className={classNames('content', {active: active})}>
        {panel.content}
      </div>
    </div>
  }
}
export default AccordionPanel;
