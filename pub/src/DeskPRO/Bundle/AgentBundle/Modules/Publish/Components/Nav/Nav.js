import React, {Component, PropTypes} from 'react';
import classNames from 'classnames';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import { NavFrame, NavFrameHeader, NavFrameBody, SectionsPane, Section, SectionHeader, SectionGroupedHeader, TabsPaneStatefulContainer, Tab,
  NestedList, ListItem, ButtonsPane, Button, ListGroupingControl }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';

export class Nav extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    loaded: PropTypes.bool.isRequired,
    articles: PropTypes.object.isRequired,
    news: PropTypes.object.isRequired,
    downloads: PropTypes.object.isRequired,
    todo: PropTypes.object.isRequired,
    grouping: PropTypes.object.isRequired,
    onGroupingChange: PropTypes.func.isRequired,
    toggleGroupingVisibility: PropTypes.func.isRequired,
    setMine: PropTypes.func.isRequired,
    onClick: PropTypes.object.isRequired,
    dpWindow: PropTypes.object.isRequired
  };

  render() {
    const { articles, news, downloads, todo, grouping, onGroupingChange, toggleGroupingVisibility, setMine, onClick, dispatch, dpWindow, loaded } = this.props;
    const currentApp = dpWindow.get('activeAppId');
    const mine = todo.get('articles').get('mine');
    const slaButtonAllClasses = classNames('sla-button', { 'selected': !mine });
    const slaButtonMineClasses = classNames('sla-button', { 'selected': mine });

    return (
      <NavFrame dispatch={dispatch.bind(this)} dpWindow={dpWindow}>
        <div part="outer">

          <ListGroupingControl title="Articles"
                               options={grouping.get('options').toArray()}
                               visible={grouping.get('visibility').get('articles')}
                               onChange={onGroupingChange('articles')}
                               attachTo={this.refs.kb}
                               close={toggleGroupingVisibility('articles')}/>

          <ListGroupingControl title="News"
                               options={grouping.get('options').toArray()}
                               visible={grouping.get('visibility').get('news')}
                               onChange={onGroupingChange('news')}
                               attachTo={this.refs.news}
                               close={toggleGroupingVisibility('news')}/>

          <ListGroupingControl title="Downloads"
                               options={grouping.get('options').toArray()}
                               visible={grouping.get('visibility').get('downloads')}
                               onChange={onGroupingChange('downloads')}
                               attachTo={this.refs.downloads}
                               close={toggleGroupingVisibility('downloads')}/>

        </div>

        <div part="inner">
          <NavFrameHeader icon="icon-dp-streamline-edit-1" currentApp={currentApp}>
            Publish
          </NavFrameHeader>
          <NavFrameBody>
            <TabsPaneStatefulContainer id="tab">
              <Tab title="KB">
                <LoadIndicator loaded={loaded}>
                  <SectionsPane>
                    <Section ref="kb">
                      <SectionGroupedHeader label="Knowledgebase"
                                            count={articles.get('count')}
                                            callback={toggleGroupingVisibility('articles')}/>
                      <NestedList items={articles.get('nested').toJS()}
                                  onClick={onClick.articles}/>
                    </Section>
                  </SectionsPane>

                  <ButtonsPane>
                    <Button title="Glossary" icon="fa-quote-left"/>
                    <Button title="Search" icon="fa-search"/>
                    <Button title="Comments" icon="fa-comments-o"/>
                  </ButtonsPane>
                </LoadIndicator>
              </Tab>

              <Tab title="News">
                <LoadIndicator loaded={loaded}>
                  <SectionsPane>
                    <Section ref="news">
                      <SectionGroupedHeader label="News"
                                            count={news.get('count')}
                                            callback={toggleGroupingVisibility('news')}/>
                      <NestedList items={news.get('nested').toJS()}
                                  onClick={onClick.news}/>
                    </Section>
                  </SectionsPane>
                </LoadIndicator>
              </Tab>

              <Tab icon="fa-download">
                <LoadIndicator loaded={loaded}>
                  <SectionsPane>
                    <Section ref="downloads">
                      <SectionGroupedHeader label="Downloads"
                                            count={downloads.get('count')}
                                            callback={toggleGroupingVisibility('downloads')}/>
                      <NestedList items={downloads.get('nested').toJS()}
                                  onClick={onClick.downloads}/>
                    </Section>
                  </SectionsPane>
                </LoadIndicator>
              </Tab>

              <Tab title="Todos">
                <LoadIndicator loaded={loaded}>
                  <SectionsPane>
                    <Section ref="todos">
                      <SectionHeader>
                        Articles
                        <div className="sla" style={{display: 'inline-block', float: 'right'}}>
                          <span className={slaButtonMineClasses} onClick={setMine(true)}>Mine</span>
                          <span className={slaButtonAllClasses} onClick={setMine(false)}>All</span>
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
                          <NestedList depth="2"
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
              </Tab>

            </TabsPaneStatefulContainer>
          </NavFrameBody>
        </div>
      </NavFrame>
    );
  }
}
