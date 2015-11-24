import React, { PropTypes } from 'react';
import { SaveTaskButton } from './SaveTaskButton';
import {
  Card,
  CardCheckbox,
  CardLine,
  CardLineLeft,
  CardLineRight
} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';
import {
  TitleForm,
  DateDue,
  CardProject,
  ProjectContainer,
  Comments,
  AssigneeContainer,
  AssigneeAvatar
} from '../../../TaskCard/index';

export class TaskCardNew extends React.Component {

  static propTypes = {
    dateDue: PropTypes.string,
    project: PropTypes.number,
    assignee: PropTypes.object,
    onChangeTitle: PropTypes.func,
    onSaveTask: PropTypes.func
  };

  onSave = event => {
    this.refs.form.onSubmit(event);
  };

  render() {
    const { dateDue, project, assignee } = this.props;
    const { onSaveTask } = this.props;

    return (
      <Card type="task">
        <SaveTaskButton onClick={this.onSave} />
        <CardCheckbox />
        <CardLine>
          <CardLineLeft>
            <div className="dpwd--card-title">
              <TitleForm ref="form" onChange={onSaveTask} />
            </div>
          </CardLineLeft>
          <CardLineRight>
            <AssigneeContainer>
              <AssigneeAvatar task={assignee} />
            </AssigneeContainer>
          </CardLineRight>
        </CardLine>

        <CardLine>
          <CardLineLeft>
            <DateDue value={dateDue} />
            <ProjectContainer project={project}>
              <CardProject />
            </ProjectContainer>
          </CardLineLeft>
          <CardLineRight>
            <Comments />
          </CardLineRight>
        </CardLine>
      </Card>
    );
  }
}
