import React, { PropTypes } from 'react';
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

  static propTypes = {
    title: PropTypes.string,
    dateDue: PropTypes.string,
    onChangeTitle: PropTypes.func
  };

  render() {
    const { title, dateDue, onChangeTitle } = this.props;

    return (
      <Card type="task">
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
