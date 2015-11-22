import React from 'react';
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
  Comments
} from '../../../TaskCard/index';

export class TaskCardNew extends React.Component {

  render() {
    return (
      <Card type="task">
        <CardCheckbox />
        <CardLine>
          <CardLineLeft>
            <Title />
          </CardLineLeft>
          <CardLineRight>
            <AssignButton />
          </CardLineRight>
        </CardLine>

        <CardLine>
          <CardLineLeft>
            <DateDue />
            <CardProject />
          </CardLineLeft>
          <CardLineRight>
            <Comments />
          </CardLineRight>
        </CardLine>
      </Card>
    );
  }
}
