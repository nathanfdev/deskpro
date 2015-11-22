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
import moment from 'moment';

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
            <DateDue value={moment().endOf('day').format()} />
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
