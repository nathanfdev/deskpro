import React, { PropTypes } from 'react';
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

    return value.get('agents') && value.get('agents').size
      || value.get('teams') && value.get('teams').size
      || value.get('departments') && value.get('departments').size;
  }

  onChange = (val) => {
    this.setState({
      value:  val,
      isOpen: false
    });

    this.props.onChange(val);
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
                    positionTarget={this}
                    positionAt="right+5 top-10"
                    collision="fit"
                    zIndex={1002}>

          <ClickOut onClickOut={this.onClose} additionalNodes={[this.refs.button, '.assign-form', '.fa-check']}>
            <AssignForm task={this.state.value} onSubmit={this.onChange} />
          </ClickOut>
        </Positioned>
      </div>
    );
  }
}
