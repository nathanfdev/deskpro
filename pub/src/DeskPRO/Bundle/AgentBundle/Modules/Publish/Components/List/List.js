import React, {Component, PropTypes} from 'react';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameMenu';
import { ListFrameContents } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import { ArticlesTableContainer } from './View/Table/ArticlesTableContainer'
import { PaginationContainer } from './PaginationContainer';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

export class List extends Component {
  static propTypes = {
    elements: PropTypes.object.isRequired,
    content: PropTypes.string.isRequired,
    loaded: PropTypes.bool.isRequired,
    pagination: PropTypes.object,
    view: PropTypes.string.isRequired
  };

  renderElements(view, elements) {
    if (!elements || elements.size === 0) {
      return 'No data to display';
    }

    switch (view) {
      case constants.VIEW_MODE_CARD:
        return this.renderListView();
      case constants.VIEW_MODE_TABLE:
        return this.renderTableView();
      default:
        throw new Error(`Unknown "${view}" view type`);
    }
  }

  renderListView() {
    const {elements, content} = this.props;
    switch (content) {
      case constants.CONTENT_ARTICLES:
        return (
          <div>
            <h1>List View</h1>
            {elements.map((element, index) => <div key={index} style={{marginTop: '20px'}}>List
              item: {element.content}</div>)}
          </div>
        );
      default:
    }
  }

  renderTableView() {
    const {elements, content} = this.props;
    switch (content) {
      case constants.CONTENT_ARTICLES:
        return (
          <ArticlesTableContainer articles={elements}/>
        );
      default:
    }
  }

  render() {
    const { elements, view, loaded, pagination } = this.props;
    const checkbox = {
      count: 1, action: ()=> {
      }
    };

    return (
      <ListFrameContainer>
        <ListFrameMenu checkbox={checkbox}/>
        <LoadIndicator loaded={loaded}
                       opacity={0}
                       width={3}>
          <ListFrameContents>
            {this.renderElements(view, elements)}
            {pagination && pagination.total_pages > 1 && <PaginationContainer/>}
          </ListFrameContents>
        </LoadIndicator>
      </ListFrameContainer>
    );
  }
}
