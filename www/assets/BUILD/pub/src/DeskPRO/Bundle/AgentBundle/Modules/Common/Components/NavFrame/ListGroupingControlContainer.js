import React, { PropTypes, Component } from 'react';
import { connect } from 'react-redux';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';

@connect()
export class ListGroupingControlContainer extends Component {
  static propTypes = {
    dispatch:                PropTypes.func.isRequired,
    id:                      PropTypes.number,
    content:                 PropTypes.string.isRequired,
    visible:                 PropTypes.bool.isRequired,
    changeListGrouping:      PropTypes.func.isRequired,
    closeGroupingVisibility: PropTypes.func.isRequired,
    options:                 PropTypes.array.isRequired,
    selected:                PropTypes.string,
    onClose:                 PropTypes.func,
    attachTo:                PropTypes.func.isRequired
  };

  shouldComponentUpdate(nextProps) {
    return (nextProps.visible !== this.props.visible) || (nextProps.content !== this.props.content);
  }

  applyFilterEditing = (e) => {
    const options = e.target.options;
    const { dispatch, content, closeGroupingVisibility, changeListGrouping, id = null } = this.props;

    for (let i = 0; i < options.length; i++) {
      if (options[i].selected) {
        const value = options[i].value;
        dispatch(changeListGrouping(value, content, id));
        dispatch(updateRoutingState('group', content, value));
        break;
      }
    }
    closeGroupingVisibility();
  };

  render() {
    const { content, options, visible, selected, attachTo, closeGroupingVisibility } = this.props;

    return (
      <Detached
        isOpen={visible}
        positionAt="right top"
        positionTarget={attachTo()}
        style={{ marginTop: '-7px', marginLeft: '7px' }}
      >
        <ClickOut onClickOut={closeGroupingVisibility}>
          <section className="sidebar-hover show">
            <div className="sidebar-hover-content">
              <div className="sidebar-hover-header">
                <i className="fa fa-tag" />
                <span>&nbsp;</span>
                <span>{content.charAt(0).toUpperCase() + content.slice(1)}</span>
              </div>
              <form>
                <p>
                  <label>Grouping Options:</label>
                  <select onChange={this.applyFilterEditing} value={selected}>
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
