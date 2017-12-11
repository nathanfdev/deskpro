import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

class AccordionPanel extends React.Component {

  static propTypes = {
    panel: PropTypes.shape({
      title: PropTypes.string.isRequired,
      count: PropTypes.arrayOf(PropTypes.any),
      icon:  PropTypes.string
    }),
    active:      PropTypes.bool,
    onItemClick: PropTypes.func
  };

  static defaultProps = {
    active: false,
    onItemClick() {}
  };

  getTitle() {
    const { icon, count } = this.props.panel;
    let { title } = this.props.panel;

    title = [title];
    if (count) {
      title.push(<span className="count" key="count"> ({count.length})</span>);
    }
    if (icon) {
      title.unshift(<i className={icon} key="icon" />);
    }

    return title;
  }

  handleItemClick = () => {
    this.props.onItemClick();
  };

  render() {
    const { panel, active } = this.props;

    return (
      <div>
        <div
          className={classNames('title', { active })}
          onClick={this.handleItemClick}
        >
          <i className="dropdown icon" />
          {this.getTitle()}
        </div>
        <div className={classNames('content', { active })}>
          {panel.content}
        </div>
      </div>
    );
  }
}

export default AccordionPanel;
