import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { allProjectsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Tasks/RecordStores/Selectors/projectSelectors';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { HiddenDateTimePicker } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/DateTime/HiddenDateTimePicker';

@connect(state => ({
  projects: allProjectsSelector(state)
}))

export class CardProject extends React.Component {

  static propTypes = {
    projectId: PropTypes.number,
    openBySingleClick: PropTypes.bool,
    onSetEditing: PropTypes.func
  };

  constructor(props) {
    super(props);

    this.state = {
      formOpened: false,
      project: props.projectId ? props.projects.get(props.projectId) : null
    };
  }

  componentWillUnmount() {
    this.isUnmounted = true;
  }

  openForm = () => {
    const { onSetEditing } = this.props;
    onSetEditing && onSetEditing(true);
    this.setState({
      formOpened: true
    });
  };

  closeForm = () => {
    const { onSetEditing } = this.props;
    onSetEditing && onSetEditing(false);
    if (this.isUnmounted) {
      return;
    }

    this.setState({
      formOpened: false
    });
  };

  render() {
    const { projects } = this.props;
    const { project } = this.state;
    const prop = {[this.props.openBySingleClick ? 'onClick' : 'onDoubleClick']: this.openForm};

    return (
      <span>
        <span className="dpw--card-disc"/>
        <span className="dpwd--card-line-item" ref="button" {...prop}>
          <i className="fa fa-book"/> {project ? project.get('title') : 'N/A'}
        </span>
        <Detached isOpen={this.state.formOpened}
                  positionTarget={this}
                  positionAt="right+5 top-10"
                  zIndex={1002}>

          <ClickOut onClickOut={this.closeForm} additionalNodes={[this.refs.button, 'assign-form']}>
            <HiddenDateTimePicker value={null} />
          </ClickOut>
        </Detached>
      </span>
    );
  }
}
