import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';

import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import ListFrameContents from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';

import { Card, CardLine, CardLineLeft, CardLineRight, CardLineFull, CardContentText, CardLineItem, CardCheckbox, CardDisc, CardTitle, CardUser, CardLabel, CardComments, CardStatusBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';

import { viewFilter } from '../../Actions/listActions';
import { listSelector } from '../../Selectors/listSelectors';

//######################################################################################################################
//# Container
//######################################################################################################################

@connect(state => ({
  list: listSelector(state),
  isLoading: state.Example.list.get('isLoading'),
  isDone: state.Example.list.get('isDone')
}))
export class WidgetListContainer extends Component {

  componentDidMount() {
    const { dispatch } = this.props;
    dispatch(viewFilter({ widgetType: this.props.widgetType }));
  }

  componentWillReceiveProps(newProps) {
    const { dispatch } = this.props;
    if (newProps.widgetType !== this.props.widgetType) {
      dispatch(viewFilter({ widgetType: newProps.widgetType }));
    }
  }

  render() {
    const { list, isLoading, isDone } = this.props;

    if (isLoading || !isDone) {
      return (<div>Loading</div>);
    }

    return (<WidgetsList widgets={list} />)
  }
}

//######################################################################################################################
//# Component
//######################################################################################################################

const Widget = ({widget}) => (
  <Card type="widget">
    <CardLine>
      <CardLineLeft>
        <CardTitle content={widget.name} />
      </CardLineLeft>
      <CardLineRight>
        {widget.inventory}
      </CardLineRight>
    </CardLine>
    <CardLine>
      {widget.agentName}
    </CardLine>
  </Card>
);

export class WidgetsList extends Component {
  static propTypes = {
    widgets: PropTypes.array.isRequired
  };

  render() {
    const { widgets } = this.props;

    return (
      <ListFrameContainer>
        <ListFrameContents>
          {widgets.map(w => <Widget widget={w} />)}
        </ListFrameContents>
      </ListFrameContainer>
    );
  }
}
