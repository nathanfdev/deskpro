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
  Title,
  AssignButton,
  DateDue,
  CardProject,
  ProjectContainer,
  Comments
} from '../../../TaskCard/index';

export class TaskCardNew extends React.Component {

  static propTypes = {
    title: PropTypes.string,
    dateDue: PropTypes.string,
    project: PropTypes.number,
    onChangeTitle: PropTypes.func,
    onSaveTask: PropTypes.func
  };

  render() {
    const { title, dateDue, project } = this.props;
    const { onChangeTitle, onSaveTask } = this.props;

    return (
      <Card type="task">
        <SaveTaskButton onClick={onSaveTask} />
        <CardCheckbox />
        <CardLine>
          <CardLineLeft>
            <Title editing value={title} onChange={onChangeTitle} />
          </CardLineLeft>
          <CardLineRight>
            <AssignButton />
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
