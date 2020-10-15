import React, { Fragment } from 'react';
import PropTypes from 'prop-types';
import { injectIntl, FormattedMessage, FormattedDate } from 'react-intl';
import Link from 'react-router/lib/Link';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import $ from 'jquery';
import { copyTextToClipboard } from 'DeskPRO/Component/Util/ClipBoard';
import { PageSummary, CommentsBlock } from '../index';
import AuthorsAvatars from './AuthorsAvatars';

class Page extends React.PureComponent {
  static propTypes = {
    intl:            PropTypes.object,
    page:            PropTypes.object,
    childrenPages:   PropTypes.array,
    pageList:        PropTypes.array,
    flashes:         PropTypes.array,
    guideSlug:       PropTypes.string,
    pageSlug:        PropTypes.string,
    sizes:           PropTypes.object,
    loaded:          PropTypes.bool,
    postComment:     PropTypes.func,
    grabPageFromApi: PropTypes.func,
  };

  static defaultProps = {
    page:     {},
    pageList: []
  };

  constructor(props) {
    super(props);
    this.anchor = React.createRef();
  }

  copyLinkToClipBoard = (e) => {
    e.preventDefault();
    const { page, guideSlug, intl } = this.props;
    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }
    const url = `${window.location.origin}${baseUrl}/guides/${guideSlug}/${page.slug}`;
    if (copyTextToClipboard(url)) {
      $(this.anchor.current).attr('data-original-title', intl.formatMessage({ id: 'helpcenter.general.copied' })).tooltip('show');
      setTimeout(() => {
        $(this.anchor.current).attr('data-original-title', intl.formatMessage({ id: 'helpcenter.general.copy_to_clipboard' })).tooltip('hide');
      }, 1000);
    }
    return false;
  };

  renderSubPage = (page) => {
    const { guideSlug, grabPageFromApi, pageList } = this.props;
    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }
    let subPage = page;
    if (Number.isInteger(subPage)) {
      subPage = pageList.find(p => p.id === subPage);
    }
    const datePublished = subPage.date_published ? subPage.date_published.replace(/T.*/, '').replace(/-/g, '/') : null;
    const dateUpdated = subPage.date_updated ? subPage.date_updated.replace(/T.*/, '').replace(/-/g, '/') : null;

    return (
      <div
        className="dp-po-guides-subtopic"
        key={subPage.id}
      >
        <Link
          className="dp-po-guides-subtopic-title"
          to={`${baseUrl}/guides/${guideSlug}/${subPage.slug}`}
          onClick={() => grabPageFromApi(subPage.slug)}
        >
          {subPage.title}
        </Link>
        <AuthorsAvatars authors={subPage.authors} max={3} />
        <div className="dp-po-guides-subtopic-dates">
          {subPage.date_published && <Fragment><FormattedMessage className="title" id="helpcenter.general.published" />: <strong><FormattedDate value={datePublished} day="numeric" month="short" year="numeric" /></strong><br /></Fragment>}
          {subPage.date_updated && <Fragment><FormattedMessage className="title" id="helpcenter.general.last_updated" />: <strong><FormattedDate value={dateUpdated} day="numeric" month="short" year="numeric" /></strong></Fragment>}
        </div>
      </div>
    );
  }

  renderSubpages = () => {
    const { page, childrenPages } = this.props;
    if (childrenPages.length === 0) {
      return null;
    }
    return (
      <div className="dp-po-guides-subtopics">
        <h3><FormattedMessage id="helpcenter.guides.pages_in" values={{ title: page.title }} /></h3>
        {childrenPages.map(child => this.renderSubPage(child))}
      </div>
    );
  }

  renderPreviousNext = () => {
    const { pageList, page, guideSlug, grabPageFromApi } = this.props;
    const index = pageList.findIndex(p => p.id === page.id);
    let previous = null;
    let previousIndex = index - 1;
    let next = null;
    let nextIndex = index + 1;
    let parentId = page.parent;
    if (!Number.isInteger(parentId)) {
      parentId = page.parent.id;
    }
    while (!previous && previousIndex >= 0) {
      if (pageList[previousIndex].no_content === '0' && ((pageList[previousIndex].parent_id === parentId) || (pageList[previousIndex].id === parentId))) {
        previous = pageList[previousIndex];
      }
      previousIndex -= 1;
    }
    const children = [];
    while (!next && nextIndex < pageList.length) {
      if (pageList[nextIndex].no_content === '0' && pageList[nextIndex].parent_id !== page.id && children.indexOf(pageList[nextIndex].parent_id) === -1) {
        next = pageList[nextIndex];
      } else if (pageList[nextIndex].parent_id === page.id || children.indexOf(pageList[nextIndex].parent_id) !== -1) {
        children.push(pageList[nextIndex].id);
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
            onClick={() => grabPageFromApi(next.slug)}
          >
            <figure className="dp-po-icon">
              <FontAwesomeIcon icon={['fal', 'angle-right']} />
            </figure>
            <span className="sup">
              <FormattedMessage id="helpcenter.guides.next_page" />
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
            onClick={() => grabPageFromApi(previous.slug)}
          >
            <span className="sup">
              <FormattedMessage id="helpcenter.guides.previous_page" />
            </span>
            <span className="title">
              {previous.title}
            </span>
            <figure className="dp-po-icon">
              <FontAwesomeIcon icon={['fal', 'angle-left']} />
            </figure>
          </Link>
        }
      </Fragment>
    );
  }

  renderComments() {
    const { page, flashes, postComment } = this.props;
    return (
      <CommentsBlock
        count={page.calc_num_comments}
        postComment={postComment}
        comments={page.comments}
        flashes={flashes}
      />
    );
  }

  renderInSection() {
    const { page, pageList } = this.props;

    if (page.parent) {
      let parent = page.parent;
      if (Number.isInteger(parent)) {
        parent = pageList.find(p => p.id === parent);
      }
      return (
        <div className="dp-po-guides-block-title-section">
          <FormattedMessage id="helpcenter.guides.in_section" values={{ section: parent.title }} />
        </div>
      );
    }
    return null;
  }

  renderContent() {
    const { page } = this.props;
    if (page.content) {
      return (
        <div
          className="dp-po-post-content dp-po-guides-block-content"
          dangerouslySetInnerHTML={{ __html: page.content }}
        />
      );
    }
    return null;
  }

  render() {
    const { page, guideSlug, pageSlug, intl, sizes, loaded } = this.props;

    const fixed = false;
    const agentBarHeight = 0;

    let baseUrl = window.DESKPRO_BASE_URL;
    if (baseUrl) {
      baseUrl = baseUrl.replace(/\/+$/, '');
    }
    const style = {};
    if (sizes && page.slug === pageSlug) {
      style.width = sizes.articleWidth;
      style.position = 'fixed';
    }
    const pageStyle = {};
    if (!loaded) {
      pageStyle.display = 'none';
    }
    const datePublished = page.date_published ? page.date_published.replace(/T.*/, '').replace(/-/g, '/') : null;
    const dateUpdated = page.date_updated ? page.date_updated.replace(/T.*/, '').replace(/-/g, '/') : null;

    return (
      <div className="dp-po-guides-block-article" id={`page_${page.slug}`} style={pageStyle}>
        <div className="row">
          <div className="col-md-9">
            <div className="dp-po-guides-block-article-left">
              <div className="dp-po-guides-block-main">

                <div className="dp-po-guides-block-header">
                  <h2 className="dp-po-guides-block-title dp-po-clipboard">
                    {page.title}
                    {page.status === 'hidden' ?
                      <span
                        className="dp-po-icon dp-info" data-toggle="tooltip"
                        title={intl.formatMessage({ id: 'helpcenter.general.viewed_by_agents_only' })} data-placement="top"
                      >
                        <FontAwesomeIcon icon={['fal', 'info-circle']} className="text-primary" />
                      </span>
                      : null}
                    <a
                      className="dp-po-clipboard-link"
                      data-toggle="tooltip"
                      data-placement="top"
                      href={`${baseUrl}/guides/${guideSlug}/${page.slug}`}
                      onClick={this.copyLinkToClipBoard}
                      ref={this.anchor}
                      title={intl.formatMessage({ id: 'helpcenter.general.copy_to_clipboard' })}
                    >
                      <span className="dp-po-icon far fa-anchor" />
                    </a>
                  </h2>
                  {this.renderInSection()}
                  <AuthorsAvatars authors={page.authors} />
                  <div className="dp-po-guides-meta">
                    {page.date_published && <Fragment><FormattedMessage id="helpcenter.general.published" />: <strong><FormattedDate value={datePublished} day="numeric" month="short" year="numeric" /></strong></Fragment>}
                    {page.date_published && page.date_updated && <span className="separator">|</span>}
                    {page.date_updated && <Fragment><FormattedMessage id="helpcenter.general.last_updated" />: <strong><FormattedDate value={dateUpdated} day="numeric" month="short" year="numeric" /></strong></Fragment>}
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
              {this.renderSubpages()}
              {this.renderPreviousNext()}
              {this.renderComments()}
            </div>
          </div>
          <div className="col-md-3 d-none d-md-block">
            <div className="dp-po-guides-block-article-right" style={style}>
              <PageSummary content={page.content} fixed={fixed} agentBarHeight={agentBarHeight} />
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default injectIntl(Page);
