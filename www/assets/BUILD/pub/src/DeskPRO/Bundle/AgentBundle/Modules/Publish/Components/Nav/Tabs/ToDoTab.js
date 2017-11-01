import PropTypes from 'prop-types';
import React, { Component } from 'react';
import classNames from 'classnames';
import { SectionsPane, Section, SectionHeader, ListItem }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { ListItemContainer } from './ListItemContainer';

export class ToDoTab extends Component {

  static propTypes = {
    todo:    PropTypes.object.isRequired,
    setMine: PropTypes.func.isRequired
  };

  render = () => {
    const { todo, setMine } = this.props;
    const mine                 = todo.get('articles').get('mine');
    const slaButtonAllClasses  = classNames('sla-button', { selected: !mine });
    const slaButtonMineClasses = classNames('sla-button', { selected: mine });
    const commentsToValidate   = todo.get('comments').get('validate');
    const commentsToReview     = todo.get('comments').get('review');
    const commentsConfig       = [
      {
        content: 'article_comments',
        type:    'articles',
        label:   'Articles',
        count:   { toValidate: commentsToValidate.get('articles'), toReview: commentsToReview.get('articles') }
      },
      {
        content: 'news_comments',
        type:    'news',
        label:   'News',
        count:   { toValidate: commentsToValidate.get('news'), toReview: commentsToReview.get('news') }
      },
      {
        content: 'download_comments',
        type:    'downloads',
        label:   'Downloads',
        count:   { toValidate: commentsToValidate.get('downloads'), toReview: commentsToReview.get('downloads') }
      }
    ];

    return (
      <SectionsPane>
        <Section ref="todos">
          <SectionHeader>
            Articles
            <div className="sla" style={{ display: 'inline-block', float: 'right' }}>
              <span className={slaButtonMineClasses} onClick={setMine.bind(this, true)}>
                Mine
              </span>
              <span className={slaButtonAllClasses} onClick={setMine.bind(this, false)}>
                All
              </span>
            </div>
          </SectionHeader>

          <ul>
            <ListItemContainer
              label="DraftArticles"
              group="articles"
              listOptions={{ content: 'articles', navItem: { hidden_status: 'draft' } }}
            >
              <ListItem
                label="Draft Articles"
                count={todo.get('articles').get('draft')}
              />
            </ListItemContainer>
            <ListItemContainer
              label="PendingArticles"
              group="article_pending_creates"
              listOptions={{ content: 'article_pending_creates', navItem: { assigned_person: mine ? 'me' : '' } }}
            >
              <ListItem
                label="Pending Articles"
                count={todo.get('articles').get('pending')}
              />
            </ListItemContainer>
          </ul>
        </Section>
        <CommentsToValidateSection config={commentsConfig} />
        <CommentsToReviewSection config={commentsConfig} />
        <Section>
          <SectionHeader>Translations</SectionHeader>
          &nbsp;
        </Section>
      </SectionsPane>
    );
  }
}

export class CommentsToValidateSection extends Component {

  static propTypes = {
    config: PropTypes.array.isRequired
  };

  render = () => {
    const { config } = this.props;

    return (
      <Section>
        <SectionHeader>Comments to validate</SectionHeader>
        <ul>
          {config.map(
            (item, index) =>
              <ListItemContainer
                key={index}
                label="ToValidate"
                group={item.content}
                listOptions={{ content: item.content, navItem: { status: 'validating' } }}
              >
                <ListItem label={item.label} count={item.count.toValidate} />
              </ListItemContainer>)
          }
        </ul>
      </Section>
    );
  }
}

export class CommentsToReviewSection extends Component {

  static propTypes = {
    config: PropTypes.array.isRequired
  };

  render = () => {
    const { config } = this.props;

    return (
      <Section>
        <SectionHeader>Comments to review</SectionHeader>
        <ul>
          {config.map(
            (item, index) =>
              <ListItemContainer
                key={index}
                label="ToReview"
                group={item.content}
                listOptions={{ content: item.content, navItem: { is_reviewed: 0 } }}
              >
                <ListItem label={item.label} count={item.count.toReview} />
              </ListItemContainer>)
          }
        </ul>
      </Section>
    );
  }
}
