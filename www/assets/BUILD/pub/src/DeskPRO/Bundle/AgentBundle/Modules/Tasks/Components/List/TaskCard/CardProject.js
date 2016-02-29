import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { ProjectsList } from 'DeskPRO/Bundle/AgentBundle/Modules/Tasks/Components/Form/Fields/ProjectsList';
import {
  BaseForm,
  Header,
  Popup,
  FieldGroup,
  FullField,
  FloatField,
  CollectionField,
  QuickFilter,
  Unassign,
  AgentsList,
  AgentTeamsList,
  DepartmentsList
} from 'DeskPRO/Bundle/AgentBundle/Modules/Tasks/Components/Form';
import { CardWidget } from './CardWidget';

export class CardProject extends CardWidget {

  static propTypes = {
    value: PropTypes.number,
    openBySingleClick: PropTypes.bool,
    onSetEditing: PropTypes.func,
    onChange: PropTypes.func.isRequired
  };

  onChange = (value) => {
    this.setState({value: value[0]});
    this.props.onChange && this.props.onChange(value[0] || null);
  };

  render() {
    const { projects } = this.props;
    const { value } = this.state;
    const project = value ? projects.get(value) : null;
    const selected = value ? [value] : [];
    const prop = {[this.props.openBySingleClick ? 'onClick' : 'onDoubleClick']: this.onOpen};

    return (
      <div style={{display: 'inline-block'}}>
        <span className="dpw--card-disc"/>
        <span className="dpwd--card-line-item" ref="button" {...prop}>
          <i className="fa fa-book"/> {project ? project.get('title') : 'N/A'}
        </span>

        <Detached isOpen={this.state.isOpen}
                  positionTarget={this}
                  positionAt="left bottom"
                  collision="fit"
                  zIndex={1002}>

          <ClickOut onClickOut={this.onClose} ignoreNodes={[this.refs.button]}>
            <Popup ref="popup" additionalClassNames="one-column">
              <CollectionField title="Project">
                <ProjectsList values={projects} onChange={this.onChange} selected={selected} />
              </CollectionField>
            </Popup>
          </ClickOut>
        </Detached>
      </div>
    );
  }
}
