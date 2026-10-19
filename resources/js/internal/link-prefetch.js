/**
 * Link prefetcher disabled to prevent request queuing, socket exhaustion,
 * and redundant duplicate server loads on dynamic uncacheable routes.
 */

export function initLinkPrefetch() {
    // Disabled intentionally: in Laravel MPA applications where responses
    // contain Cache-Control: no-cache, browser prefetching triggers duplicate
    // full controller executions without cache reuse and blocks single-threaded
    // local development servers.
}
