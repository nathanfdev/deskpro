import React, {Component, PropTypes} from 'react';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameMenu';
import { ListFrameContents } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';

export class List extends Component {
  static propTypes = {
    elements: PropTypes.object.isRequired,
    view: PropTypes.string.isRequired
  };

  render() {
    const { elements, view } = this.props;
    const checkbox = { count: 1, action: ()=>{} };

    return (
      <ListFrameContainer>
        <ListFrameMenu checkbox={checkbox}/>

        <ListFrameContents>
          {this.renderElements(view, elements)}
        </ListFrameContents>
      </ListFrameContainer>
    );
  }

  renderElements(view, elements) {
    if (!elements || elements.size === 0) {
      return 'No data to display';
    }

    switch (view) {
      case 'list':
        return this.renderListView();
      case 'table':
        return this.renderTableView();
      default:
        throw new Error(`Unknown "${view}" view type`);
    }
  }

  renderListView() {
    const {elements} = this.props;
    return (
      <div>
        <h1>List View</h1>
        {elements.map((element, index) => <div key={index} style={{marginTop: '20px'}}>List
          item: {element.content}</div>)}
      </div>
    );
  }

  renderTableView() {
    const {elements} = this.props;
    console.log('Elements', elements);
    return (
      <div>
        <h1>Table View</h1>
        {elements.map((element, index) => <div key={index} style={{marginTop: '20px'}}>Table
          row: {element.content}</div>)}
      </div>
    );
  }
}
