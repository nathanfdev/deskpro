import React, { Fragment } from 'react';
import PropTypes from 'prop-types';
import { injectIntl, FormattedMessage, FormattedDate } from 'react-intl';
import Link from 'react-router/lib/Link';
import $ from 'jquery';
import { copyTextToClipboard } from 'DeskPRO/Component/Util/ClipBoard';
import { TopicSummary, CommentsBlock } from '../index';
import AuthorsAvatars from './AuthorsAvatars';

class Topic extends React.PureComponent {
  static propTypes = {
    intl:             PropTypes.object,
    topic:            PropTypes.object,
    topicList:        PropTypes.array,
    flashes:          PropTypes.array,
    guideSlug:        PropTypes.string,
    topicSlug:        PropTypes.string,
    sizes:            PropTypes.object,
    loaded:           PropTypes.bool,
    postComment:      PropTypes.func,
    grabTopicFromApi: PropTypes.func,
  };

  static defaultProps = {
    topic:     {},
    topicList: []
  };

  constructor(props) {
    super(props);
    this.anchor = React.createRef();
  }

  copyLinkToClipBoard = (e) => {
    e.preventDefault();
    const { topic, guideSlug, intl } = this.props;
    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }
    const url = `${window.location.origin}${baseUrl}/guides/${guideSlug}/${topic.slug}`;
    if (copyTextToClipboard(url)) {
      $(this.anchor.current).attr('data-original-title', intl.formatMessage({ id: 'helpcenter.general.copied' })).tooltip('show');
      setTimeout(() => {
        $(this.anchor.current).attr('data-original-title', intl.formatMessage({ id: 'helpcenter.general.copy_to_clipboard' })).tooltip('hide');
      }, 1000);
    }
    return false;
  };

  renderSubTobic = (topic) => {
    const { guideSlug, grabTopicFromApi } = this.props;
    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }
    const datePublished = topic.date_published ? topic.date_published.replace(/T.*/, '').replace(/-/g, '/') : null;
    const dateUpdated = topic.date_updated ? topic.date_updated.replace(/T.*/, '').replace(/-/g, '/') : null;

    return (
      <div
        className="dp-po-guides-subtopic"
        key={topic.id}
      >
        <Link
          className="dp-po-guides-subtopic-title"
          to={`${baseUrl}/guides/${guideSlug}/${topic.slug}`}
          onClick={() => grabTopicFromApi(topic.slug)}
        >
          {topic.title}
        </Link>
        <AuthorsAvatars authors={topic.authors} max={3} />
        <div className="dp-po-guides-subtopic-dates">
          {topic.date_published && <Fragment><FormattedMessage className="title" id="helpcenter.general.published" />: <strong><FormattedDate value={datePublished} day="numeric" month="short" year="numeric" /></strong><br /></Fragment>}
          {topic.date_updated && <Fragment><FormattedMessage className="title" id="helpcenter.general.last_updated" />: <strong><FormattedDate value={dateUpdated} day="numeric" month="short" year="numeric" /></strong></Fragment>}
        </div>
      </div>
    );
  }

  renderSubtopics = () => {
    const { topic } = this.props;
    if (topic.children.length === 0) {
      return null;
    }
    return (
      <div className="dp-po-guides-subtopics">
        <h3><FormattedMessage id="helpcenter.guides.topics_in" values={{ title: topic.title }} /></h3>
        {topic.children.map(child => this.renderSubTobic(child))}
      </div>
    );
  }

  renderPreviousNext = () => {
    const { topicList, topic, guideSlug, grabTopicFromApi } = this.props;
    const index = topicList.findIndex(t => t.id === topic.id);
    let previous = null;
    let previousIndex = index - 1;
    let next = null;
    let nextIndex = index + 1;
    while (!previous && previousIndex >= 0) {
      if (topicList[previousIndex].no_content === '0' && ((topicList[previousIndex].parent_id === topic.parent.id) || (topicList[previousIndex].id === topic.parent.id))) {
        previous = topicList[previousIndex];
      }
      previousIndex -= 1;
    }
    const children = [];
    while (!next && nextIndex < topicList.length) {
      if (topicList[nextIndex].no_content === '0' && topicList[nextIndex].parent_id !== topic.id && children.indexOf(topicList[nextIndex].parent_id) === -1) {
        next = topicList[nextIndex];
      } else if (topicList[nextIndex].parent_id === topic.id || children.indexOf(topicList[nextIndex].parent_id) !== -1) {
        children.push(topicList[nextIndex].id);
      }
      nextIndex += 1;
    }

    if (!previous && !next) {
      return null;
    }

    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }

    return (
      <Fragment>
        {next &&
          <Link
            className="dp-po-guides-block-next-topic"
            to={`${baseUrl}/guides/${guideSlug}/${next.slug}`}
            onClick={() => grabTopicFromApi(next.slug)}
          >
            <figure className="dp-po-icon">
              <i className="fal fa-angle-right" />
            </figure>
            <span className="sup">
              <FormattedMessage id="helpcenter.guides.next_topic" />
            </span>
            <span className="title">
              {next.title}
            </span>
          </Link>
        }
        {previous &&
          <Link
            className="dp-po-guides-block-previous-topic"
            to={`${baseUrl}/guides/${guideSlug}/${previous.slug}`}
            onClick={() => grabTopicFromApi(previous.slug)}
          >
            <span className="sup">
              <FormattedMessage id="helpcenter.guides.previous_topic" />
            </span>
            <span className="title">
              {previous.title}
            </span>
            <figure className="dp-po-icon">
              <i className="fal fa-angle-left" />
            </figure>
          </Link>
        }
      </Fragment>
    );
  }

  renderComments() {
    const { topic, flashes, postComment } = this.props;
    return (
      <CommentsBlock
        count={topic.calc_num_comments}
        postComment={postComment}
        comments={topic.comments}
        flashes={flashes}
      />
    );
  }

  renderInSection() {
    const { topic } = this.props;

    if (topic.parent) {
      return (
        <div className="dp-po-guides-block-title-section">
          <FormattedMessage id="helpcenter.guides.in_section" values={{ section: topic.parent.title }} />
        </div>
      );
    }
    return null;
  }

  renderContent() {
    const { topic } = this.props;
    if (topic.content) {
      return (
        <div
          className="dp-po-post-content dp-po-guides-block-content"
          dangerouslySetInnerHTML={{ __html: topic.content }}
        />
      );
    }
    return null;
  }

  render() {
    const { topic, guideSlug, topicSlug, intl, sizes, loaded } = this.props;

    const fixed = false;
    const agentBarHeight = 0;

    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }
    const style = {};
    if (sizes && topic.slug === topicSlug) {
      style.width = sizes.articleWidth;
      style.position = 'fixed';
    }
    const topicStyle = {};
    if (!loaded) {
      topicStyle.display = 'none';
    }
    const datePublished = topic.date_published ? topic.date_published.replace(/T.*/, '').replace(/-/g, '/') : null;
    const dateUpdated = topic.date_updated ? topic.date_updated.replace(/T.*/, '').replace(/-/g, '/') : null;

    console.log(topic.date_published);
    console.log(datePublished);

    return (
      <div className="dp-po-guides-block-article" id={`topic_${topic.slug}`} style={topicStyle}>
        <div className="row">
          <div className="col-sm-9">
            <div className="dp-po-guides-block-article-left">
              <div className="dp-po-guides-block-main">

                <div className="dp-po-guides-block-header">
                  <h2 className="dp-po-guides-block-title dp-po-clipboard">
                    {topic.title}
                    {topic.status === 'hidden' ?
                      <span
                        className="dp-po-icon dp-info" data-toggle="tooltip"
                        title={intl.formatMessage({ id: 'helpcenter.general.viewed_by_agents_only' })} data-placement="top"
                      >
                        <i className="fal fa-info-circle text-primary" />
                      </span>
                      : null}
                    <a
                      className="dp-po-clipboard-link"
                      data-toggle="tooltip"
                      data-placement="top"
                      href={`${baseUrl}/guides/${guideSlug}/${topic.slug}`}
                      onClick={this.copyLinkToClipBoard}
                      ref={this.anchor}
                      title={intl.formatMessage({ id: 'helpcenter.general.copy_to_clipboard' })}
                    >
                      <span className="dp-po-icon far fa-anchor" />
                    </a>
                  </h2>
                  {this.renderInSection()}
                  <AuthorsAvatars authors={topic.authors} />
                  <div className="dp-po-guides-meta">
                    {topic.date_published && <Fragment><FormattedMessage id="helpcenter.general.published" />: <strong><FormattedDate value={datePublished} day="numeric" month="short" year="numeric" /></strong></Fragment>}
                    {topic.date_published && topic.date_updated && <span className="separator">|</span>}
                    {topic.date_updated && <Fragment><FormattedMessage id="helpcenter.general.last_updated" />: <strong><FormattedDate value={dateUpdated} day="numeric" month="short" year="numeric" /></strong></Fragment>}
                  </div>
                  {/* <div className="dp-po-guides-block-extra">*/}
                  {/*  <ul className="dp-po-guides-block-extra-list">*/}
                  {/*    <li className="dp-po-guides-block-extra-item">*/}
                  {/*      <a href="" className="dp-po-guides-block-extra-link"><i*/}
                  {/*        className="dp-po-icon fal fa-print"*/}
                  {/*      /></a>*/}
                  {/*    </li>*/}
                  {/*    <li className="dp-po-guides-block-extra-item">*/}
                  {/*      <a href="" className="dp-po-guides-block-extra-link">*/}
                  {/*        <i className="dp-po-icon fal fa-file-pdf" />*/}
                  {/*      </a>*/}
                  {/*    </li>*/}
                  {/*  </ul>*/}
                  {/* </div>*/}
                </div>
                {this.renderContent()}
              </div>
              {this.renderSubtopics()}
              {this.renderPreviousNext()}
              {this.renderComments()}
            </div>
          </div>
          <div className="col-sm-3">
            <div className="dp-po-guides-block-article-right" style={style}>
              <TopicSummary content={topic.content} fixed={fixed} agentBarHeight={agentBarHeight} />
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default injectIntl(Topic);
