import React, {Component, PropTypes} from 'react';
import classNames from 'classnames';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import { SectionsPane, Section, SectionHeader, ListItem }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from '../ListItemContainer';

export class ToDoTab extends Component {

  static propTypes = {
    loaded: PropTypes.bool.isRequired,
    todo: PropTypes.object.isRequired,
    onClick: PropTypes.object.isRequired,
    setMine: PropTypes.func.isRequired
  };

  render() {
    const { loaded, todo, onClick, setMine} = this.props;
    const mine = todo.get('articles').get('mine');
    const slaButtonAllClasses = classNames('sla-button', { 'selected': !mine });
    const slaButtonMineClasses = classNames('sla-button', { 'selected': mine });
    const commentsToValidate = todo.get('comments').get('validate');
    const commentsToReview = todo.get('comments').get('review');

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
            <SectionHeader>Comments to validate</SectionHeader>
            <ul>
              <ListItemContainer label="ToValidate"
                                 group="article_comments"
                                 listOptions={{content: 'article_comments', navItem: {status: 'validating'}}}>
                <ListItem label="Articles"
                          count={commentsToValidate.get('articles')}/>
              </ListItemContainer>
              <ListItemContainer label="ToValidate"
                                 group="news_comments"
                                 listOptions={{content: 'news_comments', navItem: {status: 'validating'}}}>
                <ListItem label="News"
                          count={commentsToValidate.get('news')}/>
              </ListItemContainer>
              <ListItemContainer label="ToValidate"
                                 group="download_comments"
                                 listOptions={{content: 'download_comments', navItem: {status: 'validating'}}}>
                <ListItem label="Downloads"
                          count={commentsToValidate.get('downloads')}/>
              </ListItemContainer>
            </ul>
          </Section>
          <Section>
            <SectionHeader>Comments to review</SectionHeader>
            <ul>
              <ListItemContainer label="ToReview"
                                 group="article_comments"
                                 listOptions={{content: 'article_comments', navItem: {is_reviewed: 0}}}>
                <ListItem label="Articles" count={commentsToReview.get('articles')}/>
              </ListItemContainer>
              <ListItemContainer label="ToReview"
                                 group="news_comments"
                                 listOptions={{content: 'news_comments', navItem: {is_reviewed: 0}}}>
                <ListItem label="News" count={commentsToReview.get('news')}
                          onClick={onClick.commentsToReview}/>
              </ListItemContainer>
              <ListItemContainer label="ToReview"
                                 group="download_comments"
                                 listOptions={{content: 'download_comments', navItem: {is_reviewed: 0}}}>
                <ListItem label="Downloads" count={commentsToReview.get('downloads')}
                          onClick={onClick.commentsToReview}/>
              </ListItemContainer>
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
