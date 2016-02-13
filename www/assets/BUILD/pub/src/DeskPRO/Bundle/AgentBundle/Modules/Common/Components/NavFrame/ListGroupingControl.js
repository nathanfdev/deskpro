import React, { PropTypes, Component } from 'react';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';

export class ListGroupingControl extends Component {
  static propTypes = {
    title: PropTypes.string.isRequired,
    visible: PropTypes.bool.isRequired,
    onChange: PropTypes.func.isRequired,
    options: PropTypes.array.isRequired,
    close: PropTypes.func.isRequired,
    selected: PropTypes.string,
    onClose: PropTypes.func,
    attachTo: PropTypes.any.isRequired
  };

  shouldComponentUpdate(nextProps) {
    return (nextProps.visible !== this.props.visible) || (nextProps.title !== this.props.title);
  }

  render() {
    const { title, onChange, close, options, visible, selected, attachTo } = this.props;

    return (
      <Detached isOpen={visible}
                positionAt="right top"
                positionTarget={attachTo}
                style={{marginTop: '-7px', marginLeft: '7px'}}>
        <ClickOut onClickOut={close}>
          <section className="sidebar-hover show">
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
                    {options.map((option, index) => <option key={index} value={option.value}>{option.label}</option>)}
                  </select>
                </p>
              </form>
            </div>
          </section>
        </ClickOut>
      </Detached>
    );
  }
}