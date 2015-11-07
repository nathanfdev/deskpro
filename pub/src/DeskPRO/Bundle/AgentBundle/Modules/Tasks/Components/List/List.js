import React, { PropTypes } from 'react';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { Controls } from './ControlBar/Controls';
import { CardView } from './View/Card/CardView';
import Loader from 'react-loader';

export class List extends React.Component {

  static propTypes = {
    tasks: PropTypes.array.isRequired,
    loaded: PropTypes.bool.isRequired
  };

  render() {
    const { tasks, loaded } = this.props;

    return (
      <ListFrameContainer>
        <Controls />

        <Loader loaded={loaded}>
          <CardView tasks={tasks} />
        </Loader>
      </ListFrameContainer>
    );
  }
}
