import React, { PropTypes } from 'react';
import ClickOutHandler from 'react-onclickout';

export class ListGroupingControl extends React.Component {
  static propTypes = {
    title: PropTypes.string.isRequired,
    visible: PropTypes.bool.isRequired,
    onChange: PropTypes.func.isRequired,
    options: PropTypes.array.isRequired,
    selected: PropTypes.string,
    onClose: PropTypes.func
  };

  componentDidMount() {
    this.onClickOutWorked = false;
  }

  onClickOut = () => {
    if (this.onClickOutWorked && this.props.onClose) {
      this.props.onClose();
    }
    this.onClickOutWorked = true;
  };

  render() {
    const { title, onChange, options, visible, selected } = this.props;
    const className = visible ? 'sidebar-hover show' : 'sidebar-hover hide';

    return (
      <ClickOutHandler onClickOut={this.onClickOut}>
        <section className={className}>
          <div className="sidebar-hover-content">
            <div className="sidebar-hover-header">
              <i className="fa fa-tag"></i>
              <span>&nbsp;</span>
              <span>{title}</span>
            </div>
            <form>
              <p>
                <label>Grouping Options:</label>
                <select onChange={onChange} value={selected}>
                  {options.map(option => <option key={option.value} value={option.value}>{option.label}</option>)}
                </select>
              </p>
            </form>
          </div>
        </section>
      </ClickOutHandler>
    );
  }

  shouldComponentUpdate(nextProps) {
    return (nextProps.visible !== this.props.visible) || (nextProps.title !== this.props.title);
  }
}