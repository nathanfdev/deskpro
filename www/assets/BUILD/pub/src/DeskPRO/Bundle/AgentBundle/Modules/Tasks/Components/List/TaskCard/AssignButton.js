import PropTypes from 'prop-types';
import React from 'react';
import { Detached as Positioned } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { AssignForm } from './AssignForm';
import { AssigneeAvatar } from './AssigneeAvatar';
import { CardWidget } from './CardWidget';
import Immutable from 'immutable';

export class AssignButton extends CardWidget {

  static propTypes = {
    onSetEditing: PropTypes.func,
    onChange:     PropTypes.func.isRequired,
    value:        PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      isOpen: false,
      value:  Immutable.fromJS({
        agents:      props.value.get('agents'),
        teams:       props.value.get('teams'),
        departments: props.value.get('departments')
      })
    };
  }

  componentWillReceiveProps(props) {
    this.setState({
      value: Immutable.fromJS({
        agents:      props.value.get('agents'),
        teams:       props.value.get('teams'),
        departments: props.value.get('departments')
      })
    });
  }

  shouldComponentUpdate(props, state) {
    return this.state.isOpen || this.state.isOpen !== state.isOpen || !Immutable.is(this.state.value, state.value);
  }

  hasAvatar() {
    const { value } = this.state;
    const list = Immutable.List();
    return value.get('agents', list).size
      || value.get('teams', list).size
      || value.get('departments', list).size;
  }

  onChange = (value) => {
    this.setState({ value });
    this.props.onChange(value);
  };

  render() {
    return (
      <div>
        <div className="dpwd--card-assigned" onClick={this.onOpen} ref="button">

          {this.hasAvatar()
            ? <AssigneeAvatar task={this.state.value} />
            : <div className="dpw--avatar-face" style={{ position: 'relative' }}>
            <i className="fa fa-caret-down" />
          </div>
          }
        </div>

        <Positioned isOpen={this.state.isOpen}
          positionTarget={this.refs.button}
          positionAt="right+5 top-10"
          collision="fit"
          zIndex={1002}
        >

          <ClickOut onClickOut={this.onClose} additionalNodes={[this.refs.button, '.assign-form', '.fa-check']}>
            <AssignForm task={this.state.value} onChange={this.onChange} />
          </ClickOut>
        </Positioned>
      </div>
    );
  }
}
