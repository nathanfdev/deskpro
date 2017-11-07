import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';

/**
 * Grouping control form
 */
export class ListGroupingForm extends Component {
  static propTypes = {
    title:     PropTypes.string.isRequired,
    options:   PropTypes.arrayOf(PropTypes.shape({
      label: PropTypes.string.isRequired,
      value: PropTypes.any.isRequired
    })),
    selected:  PropTypes.any, // will be compared to the value prop of the options
    apply:     PropTypes.func.isRequired
  };

  /**
   * Select onChange wrapper extracting the selected option and passing it together with the original event to the
   * apply callback from props
   *
   * @param e
   */
  onChange = (e) => {
    const options = e.target.options;
    for (let i = 0; i < options.length; i++) {
      if (options[i].selected) {
        this.props.apply(options[i].value, e);
      }
    }
  };

  render() {
    const { title, options, selected } = this.props;

    return (
      <section className="sidebar-hover show">
        <div className="sidebar-hover-content">
          <div className="sidebar-hover-header">
            <i className="fa fa-tag" />
            <span>&nbsp;</span>
            <span>{title.charAt(0).toUpperCase() + title.slice(1)}</span>
          </div>
          <form>
            <p>
              <select onChange={this.onChange} value={selected}>
                {options.map((option, i) => <option key={i} value={option.value}>{option.label}</option>)}
              </select>
            </p>
          </form>
        </div>
      </section>
    );
  }
}

/**
 * ListGroupingForm in a modal
 */
export class ListGroupingModal extends Component {
  static propTypes = {
    ...ListGroupingForm.propTypes,

    attachTo: PropTypes.any,
    visible:  PropTypes.bool.isRequired,
    close:    PropTypes.func.isRequired
  };

  shouldComponentUpdate(nextProps) {
    return (nextProps.visible !== this.props.visible) || (nextProps.attachTo !== this.props.attachTo);
  }

  render() {
    const { attachTo, visible, close, title, options, selected, apply } = this.props;
    const formProps = {title, options, selected, apply};
    const detachedStyle = { marginTop: '-7px', marginLeft: '7px' };

    return (
      <Detached
        positionTarget={attachTo}
        isOpen={visible}
        positionAt="right top"
        style={detachedStyle}
      >
        <ClickOut onClickOut={close}>
          <ListGroupingForm {...formProps} />
        </ClickOut>
      </Detached>
    );
  }
}
