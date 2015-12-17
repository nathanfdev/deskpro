import React, {Component, PropTypes} from 'react';
import classNames from 'classnames';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import { SectionsPane, Section, SectionHeader, NestedList, ListItem }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';

export class ToDoTab extends Component {

  static propTypes = {
    loaded: PropTypes.bool.isRequired,
    todo: PropTypes.object.isRequired,
    onClick: PropTypes.func.isRequired,
    setMine: PropTypes.func.isRequired
  };

  render() {
    const { loaded, todo, onClick, setMine} = this.props;
    const mine = todo.get('articles').get('mine');
    const slaButtonAllClasses = classNames('sla-button', { 'selected': !mine });
    const slaButtonMineClasses = classNames('sla-button', { 'selected': mine });

    return (
      <LoadIndicator loaded={loaded}>
        <SectionsPane>
          <Section ref="todos">
            <SectionHeader>
              Articles
              <div className="sla" style={{display: 'inline-block', float: 'right'}}>
                <span className={slaButtonMineClasses}
                      onClick={setMine.bind(this, true)}>
                  Mine
                </span>
                <span className={slaButtonAllClasses}
                      onClick={setMine.bind(this, false)}>
                  All
                </span>
              </div>
            </SectionHeader>

            <ul>
              <ListItem label="Draft Articles" count={todo.get('articles').get('draft')}
                        onClick={onClick.draftArticles}/>
              <ListItem label="Pending Articles" count={todo.get('articles').get('pending')}
                        onClick={onClick.pendingArticles}/>
            </ul>
          </Section>
          <Section>
            <SectionHeader>Comments</SectionHeader>

            <ul>
              <ListItem label="Comments to validate"
                        count={todo.get('comments').get('validate').get('count')}
                        onClick={onClick.allCommentsToValidate}>
                <NestedList depth={2}
                            items={todo.get('comments').get('validate').get('nested').toJS()}
                            onClick={onClick.commentsToValidate}/>
              </ListItem>
              <ListItem label="Comments to review" count={todo.get('comments').get('review')}
                        onClick={onClick.commentsToReview}/>
            </ul>
          </Section>
          <Section>
            <SectionHeader>Translations</SectionHeader>
            &nbsp;
          </Section>
        </SectionsPane>
      </LoadIndicator>
    );
  }
}
