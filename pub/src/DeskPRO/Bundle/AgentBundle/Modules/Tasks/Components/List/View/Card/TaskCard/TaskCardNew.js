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
    isValid: PropTypes.bool,
    onChangeTitle: PropTypes.func,
    onSaveTask: PropTypes.func
  };

  render() {
    const { title, dateDue, project, isValid } = this.props;
    const { onChangeTitle, onSaveTask } = this.props;

    return (
      <Card type="task">
        <SaveTaskButton onClick={onSaveTask} isValid={isValid} />
        <CardCheckbox />
        <CardLine>
          <CardLineLeft>
            <Title editing
                   value={title}
                   onChange={onChangeTitle}
                   onSave={onSaveTask} />

          </CardLineLeft>
          <CardLineRight>
            <div />
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
